<?php
use App\Model\Entity\Funding;
use Cake\Core\Configure;

$this->element('addScript', array('script' =>
    JS_NAMESPACE.".Helper.bindCancelButton();".
    JS_NAMESPACE.".Funding.bindDeleteButton(".$funding->uid.");".
    JS_NAMESPACE.".Funding.init();".
    JS_NAMESPACE.".Funding.initIsVerified('".json_encode($funding->verified_fields)."', ".Funding::getFieldsCount().");"
));
echo $this->element('jqueryTabsWithoutAjax', [
    'links' => $this->Html->getUserBackendNaviLinks($loggedUser->uid, true, $loggedUser->isOrga()),
    'selected' => $this->Html->urlFundings(),
]);
?>

<div class="profile ui-tabs custom-ui-tabs ui-widget-content">
    <div class="ui-tabs-panel">
        <?php
            echo $this->element('heading', ['first' => $metaTags['title']]);

            ?>

            <div class="color-codes-wrapper">
                <div class="is-verified">
                    bestätigt
                </div>
                <div class="is-pending">
                    Bestätigung ausstehend
                </div>
                <div class="is-missing">
                    fehlt
                </div>
                <div class="is-rejected">
                    durch Admin beanstandet
                </div>
            </div>

        <?php

        echo $this->Form->create($funding, [
            'novalidate' => 'novalidate',
            'url' => $this->Html->urlFundingsEdit($funding->workshop->uid),
            'type' => 'file',
            'id' => 'fundingForm',
        ]);

            echo $this->Form->hidden('referer', ['value' => $referer]);
            $this->Form->unlockField('referer');
            $this->Form->unlockField('verified_fields');
            echo $this->Form->hidden('Fundings.workshop.use_custom_coordinates');
            echo $this->Form->hidden('Fundings.owner_user.use_custom_coordinates');

            echo '<div class="flexbox">';

                if (!$workshopWithFundingContains->funding_is_past_events_count_ok) {
                    echo '<fieldset>';

                        echo '<legend>Aktivitätsnachweis</legend>';

                        $formattedFundingStartDate = date('d.m.Y', strtotime(Configure::read('AppConfig.fundingsStartDate')));
                        echo '<div style="margin-bottom:20px;">';
                            echo '<p>Da für die Initiative "' . h($funding->workshop->name) . '" keine Termine vor dem '.$formattedFundingStartDate.' vorhanden sind, bitten wir dich, einen Aktivitätsnachweis hochzuladen. Dieser wird dann zeitnah von uns bestätigt.</p>';
                        echo '</div>';

                        if (!empty($funding->fundinguploads_activity_proofs)) {
                            echo 'Bereits hochgeladen<br />';
                            foreach($funding->fundinguploads_activity_proofs as $fundingupload) {
                                echo '• ' . $this->Html->link($fundingupload->filename, $this->Html->urlFundinguploadDetail($fundingupload->id), ['target' => '_blank']) . '<br />';
                            }
                        }

                        echo '<div style="padding:10px;margin-top:10px;border-radius:3px;" class="'.$funding->activity_proof_status_css_class.'">Status: ' . $funding->activity_proof_status_human_readable . '</div>';
                        if ($funding->activity_proof_status != Funding::STATUS_VERIFIED) {
                            echo $this->Form->control('Fundings.fundinguploads[]', [
                                'type' => 'file',
                                'multiple' => 'multiple',
                                'label' => '',
                                'onchange' => 'document.getElementById("fundingForm").submit();',
                            ]);
                        }
                    echo '</fieldset>';

                }

                echo $this->Form->fieldset(
                    Funding::getRenderedFields(Funding::FIELDS_WORKSHOP, 'workshop', $this->Form),
                    [
                        'legend' => 'Stammdaten der Reparatur-Initiative (UID: ' . $funding->workshop->uid . ')'
                    ]
                );

                echo $this->Form->fieldset(
                    Funding::getRenderedFields(Funding::FIELDS_OWNER_USER, 'owner_user', $this->Form),
                    [
                        'legend' => 'Personenbezogene Daten Ansprechpartner*in (UID: ' . $funding->owner_user->uid . ')',
                    ]
                );

                echo $this->Form->fieldset(
                    Funding::getRenderedFields(Funding::FIELDS_SUPPORTER_ORGANIZATION, 'supporter', $this->Form),
                    [
                        'legend' => 'Stammdaten der Trägerorganisation',
                    ]
                );

                echo $this->Form->fieldset(
                    Funding::getRenderedFields(Funding::FIELDS_SUPPORTER_USER, 'supporter', $this->Form),
                    [
                        'legend' => 'Ansprechpartner*in der Trägerorganisation',
                    ]
                );

                echo $this->Form->fieldset(
                    Funding::getRenderedFields(Funding::FIELDS_SUPPORTER_BANK, 'supporter', $this->Form),
                    [
                        'legend' => 'Bankverbindung der Trägerorganisation',
                    ]
                );

                echo '</div>';

            ?>

            <div class="progress-wrapper">
                <p>Fortschritt: <span class="verified-count"></span> von <?php echo Funding::getFieldsCount(); ?> Feldern bestätigt</p>
                <div id="progress-bar"></div>
            </div>

            <?php
            $deleteButton = $this->Form->button('Förderantrag löschen', [
                'type' => 'button',
                'id' => 'delete-button',
                'class' => 'rounded red',
            ]);

            echo $this->element('cancelAndSaveButton', [
                'saveLabel' => 'Förderantrag speichern',
                'additionalButton' => $deleteButton,
            ]);

            echo $this->Form->end();

        ?>

    </div>
</div>