<?php
declare(strict_types=1);
use App\Model\Entity\Province;
?>

<style>
    div.repaired > div.box {
        border-color: <?php echo $borderColorOk; ?>;
        background-color: <?php echo $backgroundColorOk; ?>;
    }
    div.repairable > div.box {
        border-color: <?php echo $borderColorRepairable; ?>;
        background-color: <?php echo $backgroundColorRepairable; ?>;
    }
    div.not-repaired > div.box {
        border-color: <?php echo $borderColorNotOk; ?>;
        background-color: <?php echo $backgroundColorNotOk; ?>;
    }
</style>

<?php
if ($showWorkshopName) { ?>
    <style>
       h2 a {
           color: <?php echo $borderColorOk;?>;
       }
    </style>
    <h2>
        <?php
            echo $this->Html->link($workshop->name, $this->Html->urlWorkshopDetail($workshop->url), ['target' => '_blank']);
        ?>
    </h2>
<?php } ?>

<?php
if ($showName && ($city != '' || $province instanceof Province)) { ?>
    <style>
       h2 a {
           color: <?php echo $borderColorOk;?>;
       }
    </style>
    <h2>
        <?php
        if ($province instanceof Province) {
            echo $this->Html->link($province->name, $this->Html->urlEvents() . '?provinceId=' . $province->id, ['target' => '_blank']);
        }
        if ($city != '') {
            echo $this->Html->link($city, $this->Html->urlEvents($city), ['target' => '_blank']);
        }
        ?>
    </h2>
<?php } ?>

<div class="wrapper">

    <div class="repaired">
        <div class="box"></div>
        <span>Repariert (<?php echo $this->Number->precision($dataRepaired, 0); ?>)</span>
    </div>

    <div class="repairable">
        <div class="box"></div>
        <span>Reparabel (<?php echo $this->Number->precision($dataRepairable, 0); ?>)</span>
    </div>

    <div class="not-repaired">
        <div class="box"></div>
        <span>Nicht repariert (<?php echo $this->Number->precision($dataNotRepaired, 0); ?>)</span>
    </div>

</div>