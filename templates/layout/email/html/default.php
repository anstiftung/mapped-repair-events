<?php
declare(strict_types=1);
use Cake\Core\Configure;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <meta name="viewport" content="initial-scale=1.0">
        <meta name="format-detection" content="telephone=no">
        <title><?php echo Configure::read('AppConfig.platformName'); ?></title>
    </head>

    <table width="742" cellpadding="0" border="0" cellspacing="0" style="color:#000;font-family:Arial;">
        <tbody>
            <tr>
                <td align="center" valign="middle" style="padding-bottom: 20px;">
                    <a href="<?php echo Configure::read('AppConfig.serverName'); ?>">
                        <img src="<?php echo Configure::read('AppConfig.serverName'); ?>/img/core/logo.jpg" width="200" />
                    </a>
                </td>
            </tr>
            <tr>
                <td style="border-bottom: 1px solid #d6d4d4;"></td>
            </tr>
            <tr>
                <td style="padding-bottom: 20px;"></td>
            </tr>
            <tr>
                <td><?php echo $this->fetch('content'); ?></td>
            </tr>
            <tr>
                <td style="padding-top:20px;font-size:12px;">
                    <p>
                        <a href="<?php echo Configure::read('AppConfig.serverName'); ?>">Hier findest du Hilfe.</a>
                    </p>
                    <p>
                      PS: Diese E-Mail wurde automatisch erstellt.
                    </p>
                    <?php echo $this->element('emailSignatureHtml'); ?>
                </td>
            </tr>
        </tbody>
    </table>

</html>
