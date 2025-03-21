<?php
declare(strict_types=1);

namespace App\Policy;

use Cake\Http\ServerRequest;
use Authorization\Policy\RequestPolicyInterface;
use Authorization\Policy\ResultInterface;
use Cake\Core\Configure;
use Authorization\IdentityInterface;

class AdminPolicy implements RequestPolicyInterface
{

    public function canAccess(?IdentityInterface $identity, ServerRequest $request): bool|ResultInterface
    {

        if ($request->getParam('controller') == 'Fundings') {
            if (Configure::read('AppConfig.fundingsEnabled') === false) {
                return false;
            }
            return $identity !== null ? $identity->isAdmin() : false;
        }

        if ($request->getParam('controller') != 'Intern' && $identity !== null) {
            return $identity->isAdmin();
        }

        if ($request->getParam('controller') == 'Intern') {
            if (in_array($request->getParam('action'), [
                'ajaxCancelAdminEditPage',
                'ajaxMiniUploadFormDeleteImage',
                'ajaxMiniUploadFormRotateImage',
                'ajaxMiniUploadFormSaveUploadedImage',
                'ajaxMiniUploadFormSaveUploadedImagesMultiple',
                'ajaxMiniUploadFormTmpImageUpload',
                ])) {
                return $identity !== null;
            }

            if (in_array($request->getParam('action'), [
                'ajaxSetObjectStatusToDeleted',
                'ajaxDeleteObject',
                ])) {
                return $identity->isAdmin();
            }

            if (in_array($request->getParam('action'), [
                'ajaxDeleteFunding',
                ])) {
                return $identity->isAdmin() || $identity->isOrga();
            }

        }

        return false;

    }

}