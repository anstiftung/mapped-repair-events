<?php
declare(strict_types=1);

namespace App\Policy;

use Cake\Http\ServerRequest;
use Authorization\Policy\RequestPolicyInterface;
use Authorization\Policy\ResultInterface;

class AdminPolicy implements RequestPolicyInterface
{

    public function canAccess($identity, ServerRequest $request): bool|ResultInterface
    {

        if ($request->getParam('controller') != 'Intern' && $identity !== null) {
            return $identity->isAdmin();
        }

        // special logic for InternController
        if (in_array($request->getParam('action'), [
            'ajaxCancelAdminEditPage',
            'ajaxMiniUploadFormDeleteImage',
            'ajaxMiniUploadFormRotateImage',
            'ajaxMiniUploadFormSaveUploadedImage',
            'ajaxMiniUploadFormSaveUploadedImagesMultiple',
            'ajaxMiniUploadFormTmpImageUpload',
            'ajaxChangeAppObjectStatus'
        ])) {
            return $identity !== null;
        }

        return false;

    }

}