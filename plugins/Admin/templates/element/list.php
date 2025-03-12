<?php
declare(strict_types=1);
use Cake\Core\Configure;
use Cake\Utility\Hash;
use App\Model\Entity\Funding;

$this->element('addScript', array(
    'script' =>
        JS_NAMESPACE.".Helper.initTooltip('.tooltip');
    "
));

echo $this->element('highlightNavi', [
    'main' => $heading
]);

$showDeleteLink = false;
if (!isset($hideDeleteLink) || !$hideDeleteLink) {
    $showDeleteLink = true;
}

$hasUid = false;
foreach($fields as $field) {
    if (!isset($field['name'])) {
        continue;
    }
    if ($field['name'] == 'uid') {
        $hasUid = true;
    }
}

if ($showDeleteLink) {
    if (!isset($deleteMethod)) {
        if ($hasUid) {
            $deleteMethod = '/admin/intern/ajaxSetObjectStatusToDeleted';
        } else {
            $deleteMethod = '/admin/intern/ajaxDeleteObject';
        }
    }
    $this->element('addScript', [
        'script' =>
            JS_NAMESPACE.".Admin.bindDelete('".$deleteMethod."');"
    ]);
}

?>

<div class="admin">

    <div class="list">

        <?php
        if (! isset($heading)) {
            $heading = $this->request->getParam('controller');
        }
        $this->Paginator->setPaginated($objects);
        $paginatorParams = $this->Paginator->params();
        $heading .= $count = ' (' . $this->Number->precision($paginatorParams['totalCount'], 0) . ')';
        echo $this->element('heading', [
            'first' => $heading
        ]);
        ?>

        <?php
        if (! isset($optionalSearchForms)) {
            $optionalSearchForms = [];
        }
        echo $this->element('adminFilter', ['optionalSearchForms' => $optionalSearchForms]);
        ?>

        <div class="top-right-wrapper">
        
        <?php
        if (isset($selectableActions)) {
            foreach($selectableActions as $selectableAction) {
                echo $this->Html->link(
                    $selectableAction['label'],
                    'javascript:void(0);',
                    [
                        'class' => 'button selectable-action disabled',
                        'disabled' => 'disabled',
                        'data-url' => $selectableAction['url'],
                    ],
                );
            }
        }

        if (isset($newMethod)) {
            $newMethodUrl = $newMethod['url'];
            $newMethodCall = $this->Html->$newMethodUrl();
            if (isset($newMethod['param'])) {
                $newMethodCall = $this->Html->$newMethodUrl($newMethod['param']);
            }
            echo $this->Html->link( '<i class="fas fa-plus fa-border"></i>', $newMethodCall, [
                'title' => 'Neu',
                'escape' => false
            ]);
        }

        $objects = $objects->toArray();
        if (isset($emailFields) && isset($objects[0])) {
            foreach($emailFields as $emailField) {
                $rawEmails = Hash::extract($objects, '{n}.'.$emailField['field']);
                $rawEmails = array_filter($rawEmails);
                $emails = [];
                foreach($rawEmails as $email) {
                $emails[] = $email;
                }
                if (!empty($emails)) {
                    echo $this->Html->link($emailField['label'] .  ' (' . count($emails) . 'x) <i class="far fa-envelope fa-border"></i>', 'mailto:'.join(';', $emails), ['escape' => false, 'title' => 'E-Mail an alle in Liste versenden']);
                }
            }
        }
    ?>
    </div>

    <?php
    if (count($objects) == 0) {
        echo '<span style="margin-top: 10px;float: left;">Sorry, es wurden keine Daten gefunden.</span></div></div>';
        return;
    }
    ?>

        <table class="list">
            <?php
            if ($selectable) {
                echo $this->element('rowMarker/rowMarkerAll', [
                    'enabled' => true,
                ]);
            }
            foreach ($fields as $field) {

                if (isset($field['template'])) {
                    echo '<th>' . $field['label'] . '</th>';
                    continue;
                }

                // wenn das feld "label" gesetzt ist, label anzeigen und zum nächsten feld
                $label = $field['name'];
                if (isset($field['label'])) {
                    $label = $field['label'];
                }

                // nach owner.name ist nicht sortierbar wegen virtual fields...
                if (isset($field['sortable']) && ! $field['sortable']) {
                    echo '<th>' . $label . '</th>';
                    continue;
                }

                if (isset($field['type']) && in_array($field['type'], [
                    'array',
                    'habtm',
                ])) {
                    $caption = '<th>' . $label . '</th>';
                    $caption = preg_replace('/\./', ' ', $caption);
                    echo $caption;
                } else {

                    $splittedField = preg_split('/\./', $field['name']);

                    if (count($splittedField) == 1) {
                        $caption = $this->name . "\n"  . $splittedField[0];
                    }

                    if (count($splittedField) == 3) { // TODO checken ob index vorhanden - momentan nicht nötig
                        $caption = $splittedField[1] . "\n" . $splittedField[2];
                    }

                    if (isset($caption)) {
                        $caption = preg_replace('/_/', ' ', $caption);

                        // bei bestimmten feldern keine caption anzeigen
                        if (preg_match('/(email|website|feed_url|twitter_username|facebook_username)/', $label)) {
                            echo '<th class="icon"></th>';
                        } else {
                            echo '<th class="sort">';
                            echo $this->Paginator->sort($field['name'], $label);
                            echo '</th>';
                        }
                    }
                }
            }
            if ($showDeleteLink) {
                echo '<th class="icon"></th>';
            }
            if (isset($editMethod)) {
                echo '<th class="icon"></th>';
            }
            if (isset($showMethod)) {
                echo '<th class="icon"></th>';
            }

            foreach ($objects as $object) {

                $rowStatusClasses = ['status-active'];
                if (isset($object['status']) && $object['status'] == APP_OFF) {
                    $rowStatusClasses = ['status-inactive'];
                }
                echo '<tr class="' . implode(' ', $rowStatusClasses) . '">';

                if ($selectable) {
                    echo $this->element('rowMarker/rowMarker', [
                        'show' => true,
                    ]);
                }

                foreach ($fields as $field) {

                    if (isset($field['template'])) {
                        echo '<td>';
                        echo $this->element($field['template'], ['object' => $object]);
                        echo '</td>';
                        continue;
                    }

                    $value = '';
                    if (isset($field['type']) && $field['type'] == 'array') {
                        foreach ($object[$field['name']] as $key => $fieldValue) {
                            $value .= ' - ' . $fieldValue[$field['field']] . '<br />';
                        }
                    } else {
                        $splittedField = preg_split('/\./', $field['name']);
                        if (! isset($splittedField[2])) {
                            // example: categories.name
                            if (isset($field['type']) && preg_match('/habtm/', $field['type'])) { // habtm
                                $habtmi = 0;
                                $splittedField[0] = strtolower($splittedField[0]);
                                if ($object->{$splittedField[0]}) {
                                    foreach ($object->{$splittedField[0]} as $f) {
                                        $habtmi ++;
                                        $value .= $f->{$splittedField[1]};
                                        if ($habtmi < count($object->{$splittedField[0]})) {
                                            $value .= ', ';
                                        }
                                    }
                                }
                                // bei kategorien würde zu viel text in der liste sein...
                                if (isset($field['imgWithValueAsTitle'])) {
                                    if ($value != '') {
                                        $value = $this->Html->image($field['imgWithValueAsTitle'], [
                                            'title' => $value
                                        ]);
                                    }
                                }
                            } else {
                                if (isset($splittedField[1])) {
                                    if (!is_null($object->{$splittedField[0]})) {
                                        $value = $object->{$splittedField[0]}[$splittedField[1]];
                                    }
                                } else {
                                    $value = $object[$splittedField[0]];
                                }
                            }
                        } else {
                            // example: entity.association.name
                            $value = $object->{$splittedField[0]}->{$splittedField[1]}->{$splittedField[2]};
                        }
                    }

                    $idName = ($hasUid ? 'uid' : 'id');
                    $tdClass = [];
                    if ($field['name'] === $idName) {
                        $tdClass[] = 'id';
                    }
                    echo '<td class="' . join(' ', $tdClass) . '">';

                    if (! empty($field['link'])) {
                        $linkUrlMethod = $field['link']['urlMethod'];
                        $linkParamsArray = $field['link']['params'];
                        $linkParams = [];
                        foreach ($linkParamsArray as $linkParamArray) {
                            $splittedParams = preg_split('/\./', $linkParamArray);
                            if (count($splittedParams) == 2) {
                                $linkParams[] = $object[$splittedParams[0]][$splittedParams[1]];
                            } else {
                                $linkParams[] = $object[$splittedParams[0]][$splittedParams[1]][$splittedParams[2]];
                            }
                        }
                        echo $this->Html->link($value, $this->Html->$linkUrlMethod($linkParams[0])) // TODO wenn urlmethode mehrere parameter besitzt, muss nur noch hier angepasst werden
;
                    } elseif (! empty($field['values'])) {
                        if (isset($field['values']->$value)) {
                            echo $field['values']->$value;
                        }
                    } elseif (! empty($field['type'])) {

                        if ($field['type'] == 'datetime') {
                            if ($value) {
                                echo $value->i18nFormat(Configure::read('DateFormat.de.DateNTimeShort'));
                            }
                        }
                        if ($field['type'] == 'datetimeWithSeconds') {
                            if ($value) {
                                echo $value->i18nFormat(Configure::read('DateFormat.de.DateNTimeShortWithSeconds'));
                            }
                        }
                        if ($field['type'] == 'date') {
                            if ($value) {
                                echo $value->i18nFormat(Configure::read('DateFormat.de.DateShort'));
                            }
                        }
                        if ($field['type'] == 'time') {
                            $formattedTime = $value->i18nFormat(Configure::read('DateFormat.de.TimeShort'));
                            if ($formattedTime != '00:00') {
                                echo $formattedTime;
                            }
                        }
                        if ($field['type'] == 'linkedUrl') {
                            if ($value) {
                                $linkedUrl = $field['linkedUrl'];
                                echo $this->Html->link(
                                    $value,
                                    $this->Html->$linkedUrl($object['id']),
                                    [
                                        'target' => '_blank'
                                    ],
                                );
                            }
                        }
                        if (in_array($field['type'], [
                            'array',
                            'habtm',
                            'unchanged',
                        ])) {
                            echo $value;
                        }
                    } else if (isset($field['tooltip']) && $field['tooltip']) {
                        if ($value != '') {
                            echo $this->Html->link('<i class="far fa-comment fa-border"></i>', 'javascript:void(0)', [
                                'escape' => false,
                                'class' => 'tooltip',
                                'title' => h($value)
                            ]);
                        }
                    } else {
                        // Table.email automatisch mit mailto verlinkt
                        if (preg_match('/email/', $field['name']) && $value != '' && (!isset($field['type']) || $field['type'] != 'unchanged')) {
                            echo $this->Html->link('<i class="far fa-envelope fa-border"></i>', 'mailto:' . $value, [
                                'escape' => false,
                                'title' => 'E-Mail versenden'
                            ]);
                        } else if (preg_match('/website/', $field['name']) && $value != '') {
                            echo $this->Html->link('<i class="fas fa-laptop fa-border"></i>', $value, [
                                'escape' => false,
                                'title' => 'Website besuchen',
                                'target' => '_blank'
                            ]);
                        } else if (preg_match('/feed_url/', $field['name']) && $value != '') {
                            echo $this->Html->link('<i class="fas fa-rss-square fa-border"></i>', $value, [
                                'escape' => false,
                                'title' => 'Feed ansehen',
                                'target' => '_blank'
                            ]);
                        } else if (preg_match('/facebook_username/', $field['name']) && $value != '') {
                            echo $this->Html->link('<i class="fab fa-facebook fa-border"></i>', 'http://www.facebook.com/' . $value, [
                                'escape' => false,
                                'title' => 'Facebook-Seite ansehen',
                                'target' => '_blank'
                            ]);
                        } else if (preg_match('/image/', $field['name']) && $value != '') {
                            echo $this->Html->link($this->Html->image($this->Html->getThumbs50Image($value, strtolower($this->request->getParam('controller')))), $this->Html->getOriginalImage($value, strtolower($this->request->getParam('controller'))), [
                                'escape' => false,
                                'target' => '_blank'
                            ]);
                        } else {
                            if (isset($field['filterParam'])) {
                                $splittedFilterParam = preg_split('/\./', $field['filterParam']);
                                echo $this->Html->link($value, $this->request->getPath() . '?' . http_build_query([
                                        'key-standard' => $field['filterParam'],
                                        'val-standard' => $object[$splittedFilterParam[1]]
                                    ]),
                                );
                            } else {
                                echo $value;
                            }
                        }
                    }

                    echo '</td>';
                }

                if ($showDeleteLink) {
                    
                    $showDeleteLinkRow = true;
                    if (get_class($object) === Funding::class && $object->is_submitted) {
                        $showDeleteLinkRow = false;
                    }

                    echo '<td class="icon">';
                        if ($showDeleteLinkRow) {
                            echo $this->Html->link(
                                '<i class="far fa-trash-alt fa-border"></i>',
                                'javascript:void(0);',
                                [
                                    'class' => 'delete-link',
                                    'data-object-type' => lcfirst($this->request->getParam('controller')),
                                    'id' => 'delete-link-' . ($hasUid ? $object['uid'] : $object['id']),
                                    'title' => 'löschen',
                                    'escape' => false
                                ]
                            );
                        }
                    echo '</td>';
                }
                // edit link
                if (isset($editMethod)) {
                    $editMethodUrl = $editMethod['url'];
                    if (isset($editMethod['param'])) {
                        $splittedField = preg_split('/\./', $editMethod['param']);
                        $editUid = $object[$splittedField[0]][$splittedField[1]];
                    } else {
                        if (isset($object['uid'])) {
                            $editUid = $object['uid'];
                        } else {
                            $editUid = $object['id'];
                        }
                    }
                    $anchor = '';
                    if (isset($editMethod['anchor'])) {
                        $splittedField = preg_split('/\./', $editMethod['anchor']);
                        $anchor = $object[$splittedField[0]][$splittedField[1]];
                    }
                    echo '<td class="icon">';
                    echo $this->Html->link('<i class="far fa-edit fa-border"></i>', $this->Html->$editMethodUrl($editUid, $anchor), [
                        'title' => 'bearbeiten',
                        'escape' => false
                    ]);
                    echo '</td>';
                }

                // show link
                if (isset($showMethod)) {

                    echo '<td class="icon">';

                    $showMethodUrl = $showMethod['url'];

                    switch ($showMethodUrl) {
                        case 'urlEventDetail':
                            if (!empty($object['workshop'])) {
                                $showMethodCall = $this->Html->$showMethodUrl($object['workshop']->url, $object['uid'], $object['datumstart']);
                            } else {
                                $showMethodCall = null;
                            }
                            break;
                        case 'urlKnowledgeDetail':
                        case 'urlUserProfile':
                            $showMethodCall = $this->Html->$showMethodUrl($object['uid']);
                            break;
                        case 'urlWorkshopDetail':
                            $showMethodCall = $this->Html->$showMethodUrl($object['url']);
                            if (preg_match('/Laufzettel/', $heading)) {
                                $showMethodCall = $this->Html->$showMethodUrl($object->event->workshop->url);
                            }
                            break;
                        default:
                            $showMethodCall = $this->Html->$showMethodUrl($object['url']);
                            break;
                    }

                    if ($object['status'] != APP_ON) {
                        $showMethodCall = null;
                    }

                    if ($showMethodCall) {
                        echo $this->Html->link('<i class="fas fa-arrow-right fa-border"></i>', $showMethodCall, [
                            'title' => 'anzeigen',
                            'escape' => false
                        ]);
                    }
                    echo '</td>';
                }

                echo '</tr>';
            }

            ?>

        </table>

        <?php
            echo $this->element('pagination');
        ?>

    </div>

</div>