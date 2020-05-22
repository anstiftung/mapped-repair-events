<?php
    echo $this->element('heading', array('first' => __('Newsletter: Keep me updated.') ));
?>

<?php echo $this->element('newsletter/additionalInfoText'); ?>
<br />

<?php echo $this->Html->Tag('h1', __('Stay informed')); ?>
<div class="sc"></div>
<br />

<?php

    echo $this->Form->create($newsletter, [
        'novalidate' => 'novalidate',
        'url' => '/newsletter'
    ]);

    echo $this->Form->control('Newsletters.email',
        [
            'label' => __('Insert your Email')
        ]
    );

    echo $this->Form->control('Newsletters.plz',
        [
            'label' => __('Your zip code')
        ]
    );

    echo '<div class="submit"><input type="submit" value="'.__('Submit').'"></div>';

    echo $this->Form->end();

    if ($subscribed) {
        echo '<div>Deine E-Mail-Adresse <strong>(', $appAuth->getUserEmail(), ')</strong> ', __('is already subscribed to news from this workshop.'), ' <a href="/newsletter/unsubscribe/', $newsletter->unsub, '">', __('Click here to unsubscribe'), '.</a></div>';
    } else {
        echo '<div>Ich habe die <a href="'.$this->Html->urlPageDetail('datenschutz').'" target="_blank">Datenschutzerklärung</a> gelesen und stimme dieser beim Abschicken meiner Daten zu.</div>';
    }

?>
