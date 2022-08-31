<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\EmailTemplate;
use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;
use Illuminate\Support\Facades\Validator;

class MailTemplateController extends Controller
{
    protected $validations = [
        'name' => 'required|min:2',
        'subject' => 'required|min:10',
        'body' => 'required|min:10',
        'body_plain' => 'nullable|min:10',
    ];

    public function messages()
    {
        return [
        'parent_id.self_not_parent' => 'A title is required'
        ];
    }

    public function __construct()
    {
        Validator::extend(
            'self_not_parent',
            function ($attribute, $value, $parameters, $validator) {
                $data = $validator->getData();

                if (empty($data['id'])) {
                    return false;
                }

                // The parent must exist ...
                $parent = EmailTemplate::find($data['parent_id']);

                if ($parent == null) {
                    return false;
                }

                // ... and must not have a parent itself
                if ($parent->parent != null) {
                    return false;
                }

                if ($parent->id == $data['id']) {
                    return false;
                }

                // A template which acts as a parent for other templates, may not have a parent itself
                $current = EmailTemplate::find($data['id']);

                if ($current->children()->exists()) {
                    return false;
                }

                return true;
            }
        );


        $this->validations['parent_id'] = [
            'nullable',
            'exists:email_templates,id',
            'self_not_parent'
        ];
    }

    public static function inline($html)
    {
        $cssToInlineStyles = new CssToInlineStyles();

        return $cssToInlineStyles->convert($html);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return EmailTemplate::all(['id','name','subject','default']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //

        $emailTemplate = new EmailTemplate();

        $emailTemplate->forceFill($this->validate($request, $this->validations));

        $emailTemplate->body_inlined = self::inline($emailTemplate->body);

        $emailTemplate->save();

        return $emailTemplate;
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return EmailTemplate::findOrFail($id)->makeVisible('is_parent');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int                      $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $data = $this->validate($request, $this->validations);

        $model = EmailTemplate::findOrFail($id);

        $model->body_inlined = self::inline($model->body);

        $model->forceFill($data);
        $model->save();

        return $model;
    }

    public function preview(Request $request, $id)
    {
        $data = $this->validate($request, $this->validations);

        $model = EmailTemplate::findOrFail($id);

        $model->forceFill($data);

        $model->body_inlined = self::inline($model->body);

        return ['preview' => $model->render(
            [
                'main_color' => '#00ff00',
                'otp' => 1245,
                'url' => 'https://www.example.com',
                'lines' => [
                    // phpcs:ignore Generic.Files.LineLength.TooLong
                    ['line' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.'],
                    // phpcs:ignore Generic.Files.LineLength.TooLong
                    ['line' => 'Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.']
                ]
            ]
        )];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        EmailTemplate::destroy($id);

        return response(null, 200);
    }
}
