<?php
declare(strict_types=1);
?>
<tr>
    <td class="infoSheetUid">
        <?php echo $info_sheet->uid; ?>
    </td>
    <td>
        <?php echo $info_sheet->device_name; ?>
    </td>
    <td>
        <?php echo $info_sheet->category->name; ?>
    </td>
    <td>
        <?php echo !empty($info_sheet->brand) ? $info_sheet->brand->name : ''; ?>
    </td>
    <td>
        <?php echo $info_sheet->owner_user != null ? $info_sheet->owner_user->name : ''; ?>
    </td>
    <?php
        echo '<td class="icon">';
            echo $this->Html->link(
                '<i class="far fa-trash-alt fa-border"></i>',
                'javascript:void(0)',
                [
                    'title' => 'Laufzettel löschen',
                    'escape' => false,
                    'class' => 'delete-info_sheet',
                    'data-workshop-uid' => $event->workshop_uid,
                    'data-event-uid' => $info_sheet->event_uid,
                ]
            );
        echo '</td>';
    ?>
    <?php
        echo '<td class="icon">';
            echo $this->Html->link(
               '<i class="far fa-edit fa-border"></i>',
               $this->Html->urlInfoSheetEdit($info_sheet->uid, 'workshop-uid='.$event->workshop_uid.';event-uid='.$info_sheet->event_uid),
               ['title' => 'Laufzettel bearbeiten', 'escape' => false]
            );
        echo '</td>';
    ?>
</tr>