<?php
declare(strict_types=1);

/** @var \App\Model\Entity\Event $event */
?>
<div class="admin edit">
    <div class="edit">
        <?= $this->element('heading', ['first' => 'Serientermin löschen']) ?>
        <p><?= h($event->datumstart->format('d.m.Y')) ?>, <?= h($event->uhrzeitstart->format('H:i')) ?>–<?= h($event->uhrzeitend->format('H:i')) ?> Uhr</p>
        <?= $this->Form->create(null) ?>
        <?= $this->Form->control('scope', [
            'type' => 'radio', 'label' => 'Welche Termine möchtest du löschen?', 'default' => 'single',
            'options' => [
                'single' => 'Nur diesen Termin löschen',
                'all' => 'Alle Termine dieser Serie löschen',
            ],
        ]) ?>
        <?= $this->Html->link('Abbrechen', $this->Html->urlMyEvents(), ['class' => 'button']) ?>
        <?= $this->Form->button('<i class="fa fa-trash" aria-hidden="true"></i> Löschen', ['type' => 'submit', 'escapeTitle' => false]) ?>
        <?= $this->Form->end() ?>
    </div>
</div>