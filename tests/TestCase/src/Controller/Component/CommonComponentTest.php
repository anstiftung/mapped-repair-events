<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Component;

use App\Controller\Component\CommonComponent;
use Cake\Controller\ComponentRegistry;
use Cake\Controller\Controller;
use Cake\Event\Event;
use Cake\Http\ServerRequest;
use Cake\TestSuite\TestCase;

class CommonComponentTest extends TestCase
{
    public function testStartupKeepsPasswordFieldsUntouched(): void
    {
        $request = new ServerRequest([
            'post' => [
                'email' => 'john@example.com',
                'password' => 'Login&Password12',
                'Users' => [
                    'password' => 'Old&Password12',
                    'password_new_1' => 'New&Password34',
                    'password_new_2' => 'New&Password34',
                    'bio' => '  <b>Hello & welcome</b>  ',
                ],
            ],
        ]);

        $controller = new Controller($request);
        $component = new CommonComponent(new ComponentRegistry($controller));

        $component->startup(new Event('Controller.startup', $controller));

        $sanitizedRequest = $controller->getRequest();

        $this->assertSame('Login&Password12', $sanitizedRequest->getData('password'));
        $this->assertSame('Old&Password12', $sanitizedRequest->getData('Users.password'));
        $this->assertSame('New&Password34', $sanitizedRequest->getData('Users.password_new_1'));
        $this->assertSame('New&Password34', $sanitizedRequest->getData('Users.password_new_2'));
        $this->assertSame('<b>Hello &amp; welcome</b>', $sanitizedRequest->getData('Users.bio'));
    }
}