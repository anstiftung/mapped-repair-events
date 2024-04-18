<?php


    $this->element('addScript', ['script' =>
        JS_NAMESPACE.".Helper.initBoxToggle();
    "]);

    if ($worknews->hasErrors()) {
        $this->element('addScript', ['script' => "
            $('.box-toggleterminabo').trigger('click');
        "]);
    }

?>

<div id="terminabo" href="#collapseterminabo" class="box-toggle box-toggleterminabo">Termine abonnieren</div>
<div id="collapseterminabo" class="collapse">

<?php

    if (!$subscribed) {
        $this->element('addScript', ['script' =>
            JS_NAMESPACE.".Helper.updateAntiSpamField('#WorknewsForm', $('#WorkNewsForm" . $workshop->uid . "'), ".$workshop->uid.");
        "]);
        echo $this->Form->create($worknews, [
            'novalidate' => 'novalidate',
            'id' => 'WorknewsForm' . $workshop->uid,
        ]);

            $this->Form->unlockField('botEwX482');

            echo $this->Form->hidden('Worknews.workshop_uid', [
                'value' => $workshop->uid
            ]);

            echo '<span style="float:left;margin-bottom:10px;width:100%;">Ich möchte über anstehende Termine dieser Initiative per E-Mail informiert werden.</span>';
            echo $this->Form->control('Worknews.email', [
                'type' => 'email',
                'label' => false,
                'style' => 'float:left; margin:0px 10px 0px 0px;'
            ]);

            echo '<div class="submit">';
                echo $this->Form->input(__('Submit'), ['type' => 'submit']);
            echo '</div>';

        echo $this->Form->end();
    }

    echo '<div class="sc"></div>';
    if ($subscribed) {
        echo '<div>Deine E-Mail-Adresse <strong>(', $loggedUser->email, ')</strong> ', __('is already subscribed to news from this workshop.'), ' <a href="/initiativen/newsunsub/', $worknews->unsub, '">', __('Click here to unsubscribe'), '.</a></div>';
    }

?>

</div>

<div class="sc"></div>