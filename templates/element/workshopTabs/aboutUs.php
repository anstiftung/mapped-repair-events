
<div id="tabs-1">
	<div class="address-wrapper">

<?php

use App\Controller\Component\StringComponent;

if ($workshop->image != '') echo '<img title="' . h($workshop->name). '" alt="' . h($workshop->name) . '" class="detail-image"
src="' . $this->Html->getThumbs150Image($workshop->image, 'workshops') . '" />';

else echo '<img alt="' . $workshop->name . '" class="detail-image"
src="' . $this->Html->getThumbs150Image('rclogo-150.jpg', 'workshops') . '" />';

echo '<strong>' . $workshop->name . '</strong><br>';

if ($workshop->street != '') {
    echo StringComponent::prepareTextForHTML($workshop->street) . ', ';
}

if ($workshop->city != '') {
    echo $workshop->zip . ' ' . $workshop->city;
}
if ($workshop->adresszusatz != '') {
    echo '<br>' . $workshop->adresszusatz;
}

if (isset($workshop->country->name_de)) {
    echo '<br>' . $workshop->country->name_de;
}

echo '<br /><br />';
echo $this->Html->Tag('strong', __('Email:') . '&nbsp;');
echo StringComponent::hide_email($workshop->email);

if ($workshop->website != '') {
    echo '<br />';
    echo $this->Html->Tag('strong', __('Website:') . '&nbsp;');
    echo $this->Html->link($workshop->website, $workshop->website, [
        'target' => '_blank'
    ]);
}

if ($workshop->facebook_username != '') {
    echo '<br />';
    echo $this->Html->Tag('strong', __('Facebook:') . '&nbsp;');
    echo $this->Html->link(
        $this->Html->getFacebookUrl($workshop->facebook_username),
        $this->Html->getFacebookUrl($workshop->facebook_username),
        [
            'target' => '_blank'
        ]
    );
}

if ($workshop->traeger != '') {
    echo '<br />';
    echo $this->Html->Tag('strong', __('Supporter of this repair initiative') . ':&nbsp;');
    echo $workshop->traeger;
    echo '<br />';
}

if ($workshop->rechtsform != '') {
    echo '<br />';
    echo $this->Html->Tag('strong', __('Legal status this repair initiative') . ':&nbsp;');
    echo $workshop->rechtsform;
    echo '<br />';
}

if ($workshop->rechtl_vertret != '') {
    echo '<br />';
    echo $this->Html->Tag('strong', __('Legally represented by') . ':&nbsp;');
    echo $workshop->rechtl_vertret;
}

if ($workshop->additional_contact != '') {
    echo '<br /><br /><strong>' . __('Additional Contact possibilities: ') . '</strong>&nbsp;' . StringComponent::prepareTextForHTML($workshop->additional_contact) . '<br><br>';
}

echo '<div class="sc"></div>';

if (!$this->request->getSession()->read('isMobile')) {

    foreach ($orgaTeam as $user) {
        echo '<div class="orgaansprech">';
            echo '<a class="green" title="Zum Profil" href="'.$this->Html->urlUserProfile($user->uid) . '">';
                echo $this->Html->getUserProfileImage($user);
                echo '<b style="color:#727374;">' . __('CONTACT PERSON') . '</b><br />';
                if ($user->firstname != '' || $user->lastname != '') {
                    if ($user->firstname != '') {
                        echo $user->firstname . ' ';
                    }
                    if ($user->lastname != '') {
                        echo $user->lastname;
                    }
                } else {
                    echo $user->nick;
                }
                echo '<br />';
            echo '</a>';
        echo '</div>';
    }

}

if(!empty($workshop->categories)) { ?>
	<div class="skill-icon-wrapper">
    	<h2>Unsere Reparaturbereiche</h2>
        <div class="skill-icons">
        <?php 
            foreach($workshop->categories as $category) {
                echo '<div title="'.$category->name.'" class="sklill_icon '.$category->icon.'"></div>';
            }
        ?>
        </div>
    </div>
<?php }

if ($workshop->text != '') {
    echo '<div class="sc"></div>';
    echo $this->Html->Tag('h2', __('Workshop Detail headline Description'));
    echo '<br />';
    $charCount = 220;
    echo '<article class="preview">' . $this->Text->truncate($workshop->text, $charCount, [
        'ending' => '...',
        'exact'  => true,
        'html'   => true,
    ]) . '</article>';
    if (strlen($workshop->text) > $charCount) {
        $this->element('addScript', ['script' =>
            JS_NAMESPACE . ".Helper.bindShowMoreLink();
        "]);
        echo '<a href="javascript:void(0);" class="show-more-link">Weiterlesen</a>';
        echo '<article class="full">' . $workshop->text . '</article><br />';
    }
    echo '<div class="sc"></div>';
}
?>

</div>
</div>