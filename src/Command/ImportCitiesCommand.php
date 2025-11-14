<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Datasource\ConnectionManager;
use Cake\Datasource\ConnectionInterface;
use Cake\Database\Connection;

class ImportCitiesCommand extends Command
{
    private const BATCH_SIZE = 1000;
    private const FILES_TO_IMPORT = ['DE.txt', 'CH.txt', 'AT.txt'];
    
    public function execute(Arguments $args, ConsoleIo $io)
    {
        $connection = ConnectionManager::get('default');
        $dataPath = ROOT . DS . 'tmp' . DS . 'data' . DS;
        
        // Get already imported geoname IDs to enable resume capability
        $importedIds = $this->getImportedGeonameIds($connection);
        $io->out(sprintf('Found %d already imported records', count($importedIds)));
        
        foreach (self::FILES_TO_IMPORT as $filename) {
            $filePath = $dataPath . $filename;
            
            if (!file_exists($filePath)) {
                $io->warning(sprintf('File not found: %s', $filePath));
                continue;
            }
            
            $io->out(sprintf('Processing file: %s', $filename));
            $this->importFile($filePath, $connection, $io, $importedIds);
        }
        
        $io->success('Import completed successfully');
        return static::CODE_SUCCESS;
    }
    
    /**
     * @return array<int, bool>
     */
    private function getImportedGeonameIds(mixed $connection): array
    {
        $query = $connection->execute('SELECT geonameid FROM cities');
        $ids = [];
        
        foreach ($query as $row) {
            $ids[$row['geonameid']] = true;
        }
        
        return $ids;
    }
    
    /**
     * @param array<bool> $importedIds
     */
    private function importFile(string $filePath, mixed $connection, ConsoleIo $io, array &$importedIds): void
    {
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            $io->error(sprintf('Could not open file: %s', $filePath));
            return;
        }
        
        $batch = [];
        $lineNumber = 0;
        $importedCount = 0;
        $skippedCount = 0;
        
        while (($line = fgets($handle)) !== false) {
            $lineNumber++;
            $data = $this->parseLine($line);
            
            if (!$data) {
                continue;
            }
            
            // Only import records with feature_class "A" (administrative boundaries)
            if ($data['feature_class'] !== 'P') {
                continue;
            }
            
            // Skip if already imported (resume capability)
            if (isset($importedIds[$data['geonameid']])) {
                $skippedCount++;
                continue;
            }
            
            $batch[] = $data;
            
            // Insert batch when size is reached
            if (count($batch) >= self::BATCH_SIZE) {
                $this->insertBatch($connection, $batch);
                $importedCount += count($batch);
                
                // Mark as imported
                foreach ($batch as $item) {
                    $importedIds[$item['geonameid']] = true;
                }
                
                $io->out(sprintf('Imported %d records (skipped %d already imported, line %d)', 
                    $importedCount, $skippedCount, $lineNumber));
                $batch = [];
            }
        }
        
        // Insert remaining records
        if (!empty($batch)) {
            $this->insertBatch($connection, $batch);
            $importedCount += count($batch);
            
            foreach ($batch as $item) {
                $importedIds[$item['geonameid']] = true;
            }
        }
        
        fclose($handle);
        $io->success(sprintf('File completed: imported %d new records, skipped %d already imported', 
            $importedCount, $skippedCount));
    }
    
    /**
     * @return array<string, mixed>|null
     */
    private function parseLine(string $line): ?array
    {
        // Geonames format is tab-separated
        $fields = explode("\t", trim($line));
        
        // Ensure we have all 19 fields
        if (count($fields) < 19) {
            return null;
        }
        
        return [
            'geonameid' => (int)$fields[0],
            'name' => $this->nullIfEmpty($fields[1]),
            'asciiname' => $this->nullIfEmpty($fields[2]),
            'alternatenames' => $this->nullIfEmpty($fields[3]),
            'latitude' => $this->parseDouble($fields[4]),
            'longitude' => $this->parseDouble($fields[5]),
            'feature_class' => $this->nullIfEmpty($fields[6]),
            'feature_code' => $this->nullIfEmpty($fields[7]),
            'country_code' => $this->nullIfEmpty($fields[8]),
            'cc2' => $this->nullIfEmpty($fields[9]),
            'admin1_code' => $this->nullIfEmpty($fields[10]),
            'admin2_code' => $this->nullIfEmpty($fields[11]),
            'admin3_code' => $this->nullIfEmpty($fields[12]),
            'admin4_code' => $this->nullIfEmpty($fields[13]),
            'population' => $this->parseInteger($fields[14]),
            'elevation' => $this->parseInteger($fields[15]),
            'dem' => $this->parseInteger($fields[16]),
            'timezone' => $this->nullIfEmpty($fields[17]),
            'modification_date' => $this->parseDate($fields[18]),
        ];
    }
    
    /**
     * @param list<array<string, mixed>> $batch
     */
    private function insertBatch(mixed $connection, array $batch): void
    {
        if (empty($batch)) {
            return;
        }
        
        $fields = array_keys($batch[0]);
        $placeholders = [];
        $values = [];
        
        foreach ($batch as $row) {
            $rowPlaceholders = [];
            foreach ($fields as $field) {
                $rowPlaceholders[] = '?';
                $values[] = $row[$field];
            }
            $placeholders[] = '(' . implode(', ', $rowPlaceholders) . ')';
        }
        
        $sql = sprintf(
            'INSERT INTO cities (%s) VALUES %s',
            implode(', ', $fields),
            implode(', ', $placeholders)
        );
        
        $connection->execute($sql, $values);
    }
    
    private function nullIfEmpty(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        return $value;
    }
    
    private function parseDouble(?string $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }
        return (float)$value;
    }
    
    private function parseInteger(?string $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }
        return (int)$value;
    }
    
    private function parseDate(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        // Geonames uses YYYY-MM-DD format which is compatible with MySQL DATE
        return $value;
    }
}
