<?php

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Core\Configure;
use Cake\I18n\Number;
use App\Mailer\AppMailer;

class BackupUserUploadsCommand extends Command
{

    public function execute(Arguments $args, ConsoleIo $io)
    {

        ini_set('max_execution_time', 300);
        ini_set('memory_limit', '512M');

        $folderToSaveBackup = ROOT.DS.'backups'.DS.'user-uploads';
        $isFullBackup = date('d') == 11;

        if(!is_dir($folderToSaveBackup)) {
            $io->out(' ', 1);
            $io->out('Will create "'.$folderToSaveBackup.'" directory!');
            if(mkdir($folderToSaveBackup,0755,true)){
                $io->out('Directory created!');
            }
        }

        $filePrefixes = ['MAINTAINER'];
        if (!empty(Configure::read('AppConfig.additionalBackupNotificationReceivers'))) {
            $filePrefixes[] = key(Configure::read('AppConfig.additionalBackupNotificationReceivers'));
        }

        $foldersToBackup = [
            ROOT . DS . 'files_private',
        ];

        if ($isFullBackup) {
            $foldersToBackup[] = ROOT . DS . 'webroot' . DS . 'files';
        }

        foreach($filePrefixes as $filePrefix) {

            $zip = new \ZipArchive;
            $zipFilename = $folderToSaveBackup.DS.$filePrefix.'_backup_user_uploads_'.date('Y-m-d_H-i-s').'.zip';
            $zip->open($zipFilename, \ZipArchive::CREATE);

            foreach($foldersToBackup as $folderToBackup) {

                $files = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($folderToBackup),
                    \RecursiveIteratorIterator::LEAVES_ONLY
                );

                foreach ($files as $name => $file) {
                    $fileWithPath = $file->getRealPath();
                    if (!is_dir($fileWithPath) && $fileWithPath != '') {
                        $zip->addFile($fileWithPath);
                    }
                }
            }

            $zip->close();
        }

        $size = Number::toReadableSize(filesize($zipFilename));
        $subject = 'Tägliches User-Uploads-Backup ('.$size.') für '. Configure::read('AppConfig.htmlHelper')->getHostName() . ' erfolgreich erstellt.';
    
        if ($isFullBackup) {

            $to = [Configure::read('AppConfig.debugMailAddress')];
            if (!empty(Configure::read('AppConfig.additionalBackupNotificationReceivers'))) {
                $to = array_merge($to, array_values(Configure::read('AppConfig.additionalBackupNotificationReceivers')));
            }
        
            $email = new AppMailer();
            $email->viewBuilder()->setTemplate('backup_user_uploads');
            $email->setTo($to);
            $email->setSubject($subject);
            $email->setViewVars([
                'foldersToBackup' => $foldersToBackup,
                'size' => $size,
            ]);
            $email->addToQueue();
        }

        $io->out($subject);

        return static::CODE_SUCCESS;

    }

}
?>