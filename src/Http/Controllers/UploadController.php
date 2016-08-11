<?php

namespace SleepingOwl\Admin\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use SleepingOwl\Admin\Contracts\ModelConfigurationInterface;
use SleepingOwl\Admin\Form\Element\File;
use SleepingOwl\Admin\Model\Upload;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Validator;

class UploadController extends Controller
{
    /**
     * @param Request $request
     * @param ModelConfigurationInterface $model
     * @param string $field
     * @param string|integer|null $id
     *
     * @return JsonResponse
     */
    public function upload(Request $request, ModelConfigurationInterface $model, $field, $id = null)
    {
        if (! is_null($id)) {
            $item = $model->getRepository()->find($id);
            if (is_null($item) || ! $model->isEditable($item)) {
                return new JsonResponse([
                    'message' => trans('lang.message.access_denied'),
                ], 403);
            }

            $form = $model->fireEdit($id);
        } else {
            if (! $model->isCreatable()) {
                return new JsonResponse([
                    'message' => trans('lang.message.access_denied'),
                ], 403);
            }

            $form = $model->fireCreate();
        }

        /** @var File $element */
        if (is_null($element = $form->getElement($field))) {
            throw new NotFoundHttpException('Field not found');
        }

        $rules = $element->getUploadValidationRules();
        $messages = $element->getUploadValidationMessages();
        $labels = $element->getUploadValidationLabels();

        $validator = Validator::make($request->all(), $rules, $messages, $labels);

        $element->customValidation($validator);

        if ($validator->fails()) {
            return new JsonResponse([
                'message' => trans('lang.message.validation_error'),
                'errors' => $validator->errors()->get('file'),
            ], 400);
        }

        $file = $request->file('file');

        $uploadedFile = Upload::create([
            'file' => $file,
        ]);

        return new JsonResponse([
            'url' => $uploadedFile->file_url,
            'value' => $uploadedFile->file,
        ]);
    }
}