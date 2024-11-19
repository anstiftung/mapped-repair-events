<?php
use App\Model\Entity\Funding;
use Cake\Core\Configure;

$this->element('addScript', ['script' =>
    JS_NAMESPACE.".Helper.bindCancelButton();".
    JS_NAMESPACE.".Funding.bindDeleteButton(".$funding->uid.");".
    JS_NAMESPACE.".Funding.init();".
    JS_NAMESPACE.".Funding.initIsVerified('".json_encode($funding->verified_fields)."');".
    JS_NAMESPACE.".Funding.updateProgressBar(" . $funding->verified_fields_count . ",  ".$funding->required_fields_count.");"
]);
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
            $this->Form->unlockField('delete_fundinguploads');
            $this->Form->unlockField('files_fundinguploads');
            echo $this->Form->hidden('Fundings.workshop.use_custom_coordinates');
            echo $this->Form->hidden('Fundings.owner_user.use_custom_coordinates');

            echo '<div class="flexbox">';

                if ($funding->workshop->funding_activity_proof_required) {
                    echo '<fieldset>';

                        echo '<legend>Aktivitätsnachweis</legend>';

                        $formattedFundingStartDate = date('d.m.Y', strtotime(Configure::read('AppConfig.fundingsStartDate')));
                        echo '<div style="margin-bottom:10px;">';
                            echo '<p>Da für die Initiative "' . h($funding->workshop->name) . '" keine Termine vor dem '.$formattedFundingStartDate.' vorhanden sind, bitten wir dich, maximal 5 Aktivitätsnachweise hochzuladen. Dieser wird dann zeitnah von uns bestätigt.</p>';
                        echo '</div>';

                        if (!empty($funding->fundinguploads_activity_proofs)) {
                            echo 'Bereits hochgeladen<br />';
                            $i = 0;
                            foreach($funding->fundinguploads_activity_proofs as $fundingupload) {
                                $activityProofFilenameLabel = $this->Html->link('Vorschau', $this->Html->urlFundinguploadDetail($fundingupload->id), ['target' => '_blank']);
                                echo $this->Form->control('Fundings.fundinguploads.'.$i.'.id', ['type' => 'hidden']);
                                echo $this->Form->control('Fundings.fundinguploads.'.$i.'.owner', ['type' => 'hidden']);
                                echo $this->Form->control('Fundings.fundinguploads.'.$i.'.type', ['type' => 'hidden']);
                                echo $this->Form->control('Fundings.fundinguploads.'.$i.'.filename', ['label' => $activityProofFilenameLabel, 'readonly' => true, 'class' => 'is-upload no-verify', 'escape' => false]);
                                $i++;
                            }

                            echo '<div style="padding:10px;margin-top:10px;border-radius:3px;" class="' . $funding->activity_proof_status_css_class . '">';
                                echo '<p>' . $funding->activity_proof_status_human_readable . '</p>';
                                if ($funding->activity_proof_comment != '' && $funding->activity_proof_status == Funding::STATUS_REJECTED) {
                                    echo '<p style="padding:10px;margin-top:10px;border:1px solid #fff;border-radius:3px;">' . h($funding->activity_proof_comment) . '</p>';
                                }
                            echo '</div>';

                        }

                        if ($funding->activity_proof_status != Funding::STATUS_VERIFIED) {

                            $this->element('addScript', ['script' =>
                                JS_NAMESPACE.".Funding.initBindDeleteFundinguploads();"
                            ]);

                            echo '<div style="margin-top:10px;">';
                                echo '<p>Nur PDF, JPG und PNG-Dateien sind erlaubt, und jede Datei muss unter 5 MB sein.</p>';
                            echo '</div>';

                            echo $this->Form->control('Fundings.files_fundinguploads[]', [
                                'type' => 'file',
                                'multiple' => 'multiple',
                                'label' => '',
                                'accept' => '.jpg, .png, .pdf, .jpeg', 
                            ]);

                            echo $this->Form->button('Dateien hochladen', [
                                'type' => 'submit',
                                'class' => 'upload-button rounded',
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
                    Funding::getRenderedFields(Funding::FIELDS_FUNDINGSUPPORTER_ORGANIZATION, 'fundingsupporter', $this->Form),
                    [
                        'legend' => 'Stammdaten der Trägerorganisation',
                    ]
                );

                echo $this->Form->fieldset(
                    Funding::getRenderedFields(Funding::FIELDS_FUNDINGSUPPORTER_USER, 'fundingsupporter', $this->Form),
                    [
                        'legend' => 'Ansprechpartner*in der Trägerorganisation',
                    ]
                );

                echo $this->Form->fieldset(
                    Funding::getRenderedFields(Funding::FIELDS_FUNDINGSUPPORTER_BANK, 'fundingsupporter', $this->Form),
                    [
                        'legend' => 'Bankverbindung der Trägerorganisation',
                    ]
                );
 
                echo '<fieldset class="fundingbudgetplan">';
                    echo '<legend>Kostenplan</legend>';
                    foreach($funding->fundingbudgetplans as $fundingbudgetplanIndex => $fundingbudgetplan) {
                        echo '<div class="row">';
                            echo Funding::getRenderedFields(Funding::FIELDS_FUNDINGBUDGETPLAN, 'fundingbudgetplans.'.$fundingbudgetplanIndex, $this->Form);
                        echo '</div>';
                    }
                echo '</fieldset>';

                echo '</div>';

            ?>

            <div class="progress-wrapper">
                <p>Fortschritt: <span class="verified-count"></span> von <?php echo $funding->required_fields_count; ?> Feldern bestätigt</p>
                <div id="progress-bar"></div>
            </div>

            <?php
            $deleteButton = $this->Form->button('Förderantrag löschen', [
                'type' => 'button',
                'id' => 'delete-button',
                'class' => 'rounded red',
            ]);

            echo $this->element('cancelAndSaveButton', [
                'saveLabel' => 'Förderantrag zwischenspeichern',
                'additionalButton' => $deleteButton,
            ]);

            echo $this->Form->end();

        ?>

    </div>
</div>