<?php
namespace App\Controller\Component;

class AntiSpamComponent extends AppComponent
{

    public function generateKey()
    {
        return StringComponent::createRandomString(ANTI_SPAM_KEY_LENGTH);
    }

}

?>