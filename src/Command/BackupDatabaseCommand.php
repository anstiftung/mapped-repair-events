<?php

namespace App\Command;

use Cake\I18n\Number;
use Cake\Mailer\Mailer;
use Cake\Core\Configure;
use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Datasource\ConnectionManager;
use App\Controller\Component\StringComponent;

class BackupDatabaseCommand extends Command
{

    public function execute(Arguments $args, ConsoleIo $io)
    {

        ini_set('max_execution_time', 300);
        ini_set('memory_limit', '256M');

        $dbConfig = ConnectionManager::getConfig('default');

        $backupdir = ROOT.DS.'backups'.DS.'db-backups';
        $preparedHostWithoutProtocol = Configure::read('AppConfig.htmlHelper')->getHostWithoutProtocol(Configure::read('AppConfig.serverName'));
        $preparedHostWithoutProtocol = str_replace('www.', '', $preparedHostWithoutProtocol);
        $preparedHostWithoutProtocol = StringComponent::slugify($preparedHostWithoutProtocol);
        $filename = $backupdir . DS . $preparedHostWithoutProtocol . '-' . date('Y-m-d_H-i-s', time()) . '.bz2';

        if (! is_dir($backupdir)) {
            $io->out(' ', 1);
            $io->out('Will create "' . $backupdir . '" directory!');
            if (mkdir($backupdir, 0755, true)) {
                $io->out('Directory created!');
            }
        }

        $dsnString = "mysql:host=". $dbConfig['host'].";dbname=".$dbConfig['database'];
        if (isset($dbConfig['port'])) {
            $dsnString .= ";port=".$dbConfig['port'];
        }

        $settings = [
            'default-character-set' => 'utf8mb4',
            'compress' => 'Bzip2',
            'add-drop-table' => true,
            'exclude-tables' => [
                'queued_jobs',
            ],
        ];
        $dump = new \Druidfi\Mysqldump\Mysqldump($dsnString, $dbConfig['username'], $dbConfig['password'], $settings);
        $dump->start($filename);

        $message = 'Datenbank-Backup erfolgreich ('.Number::toReadableSize(filesize($filename)).').';

        // email zipped file
        $mailer = new Mailer('default');
        $mailer->setTo(Configure::read('AppConfig.debugMailAddress'))
            ->setSubject($message . ': ' . Configure::read('AppConfig.serverName'))
            ->setAttachments([
                $filename,
            ])
            ->send();
        $io->out($message);

        return static::CODE_SUCCESS;

    }

}
?>