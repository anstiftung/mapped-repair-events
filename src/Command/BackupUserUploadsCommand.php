<?php

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Core\Configure;
use Cake\I18n\Number;
use Cake\Mailer\Mailer;

class BackupUserUploadsCommand extends Command
{

    public function execute(Arguments $args, ConsoleIo $io)
    {

        ini_set('max_execution_time', 300);
        ini_set('memory_limit', '512M');

        $folderToSaveBackup = ROOT.DS.'backups'.DS.'user-uploads';

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

        foreach($filePrefixes as $filePrefix) {

            $zip = new \ZipArchive;
            $zipFilename = $folderToSaveBackup.DS.$filePrefix.'_backup_user_uploads_'.date('Y-m-d_H-i-s').'.zip';
            $zip->open($zipFilename, \ZipArchive::CREATE);

            $folderToBackup = ROOT.DS.'webroot'.DS.'files';
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

            $zip->close();
        }

        $message = 'Monatliches User-Uploads-Backup ('.Number::toReadableSize(filesize($zipFilename)).') für '. Configure::read('AppConfig.htmlHelper')->getHostName() . ' erfolgreich erstellt.';

        $to = [Configure::read('AppConfig.debugMailAddress')];
        if (!empty(Configure::read('AppConfig.additionalBackupNotificationReceivers'))) {
            $to = array_merge($to, array_values(Configure::read('AppConfig.additionalBackupNotificationReceivers')));
        }
     
        $mailer = new Mailer('default');
        $mailer->setTo($to);
        $mailer->setSubject($message);
        $mailer->send();
        $io->out($message);

        return static::CODE_SUCCESS;

    }

}
?>