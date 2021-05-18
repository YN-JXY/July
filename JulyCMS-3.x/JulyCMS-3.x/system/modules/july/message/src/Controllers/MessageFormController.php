<?php

namespace July\Message\Controllers;

use App\EntityField\FieldBase;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use July\Message\MessageField;
use July\Message\MessageForm;

class MessageFormController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('message::form.index', [
            'models' => MessageForm::index(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('message::form.create-edit', $this->getCreationContext());
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \July\Message\MessageForm  $form
     * @return \Illuminate\Http\Response
     */
    public function edit(MessageForm $form)
    {

        $data = $this->getCreationContext();
        $data['model'] = $form->gather();

        $fields = $form->fields->map(function($field) {
            return $field->getMeta();
        });
        $data['context']['fields'] = $fields->sortBy('delta')->keyBy('id')->all();

        $data['context']['mode'] = 'edit';
        return view('message::form.create-edit', $data);
    }

    /**
     * 获取 create 所需渲染环境
     *
     * @return array
     */
    protected function getCreationContext()
    {
        $fields = MessageField::index()->map(function(FieldBase $field) {
            return $field->getMeta();
        });

        return [
            'model' => MessageForm::template(),
            'context' => [
                'entity_name' => MessageForm::getEntityClass()::getEntityName(),
                'fields' => [],
                'all_fields' => $fields->all(),
                'field_template' => MessageField::template(),
                'content_langcode' => langcode('content'),
                'mode' => 'create',
            ],
            'langcode' => langcode('content'),
        ];
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // 创建类型
        MessageForm::create($request->all());
        $form = $_SERVER['DOCUMENT_ROOT'].'/themes/frontend/template/message/form';
        $content = $_SERVER['DOCUMENT_ROOT'].'/themes/frontend/template/message/content';
        if (!is_dir( $form )) {
            mkdir($form,0777,true);
        }
        if (!is_dir( $content )) {
            mkdir($content,0777,true);
        }
        fopen($form."/". $request->id.".twig", "w");
        fopen($content."/".$request->id.".twig", "w");

        $email = DB::table('message_field_message_form')->select('field_id')->where('mold_id',$request->id)->get();

        $content_value = "";
        foreach ($email as $key => $value) {
           $content_value.="{{fields.".$value->field_id.".label}}:{{fields.".$value->field_id.".value}}\n";
        }
        $content_values = "\nIPLocation: {{ message.location }}\nIP: {{ message.ip }}\nUser Agent: {{ message.user_agent }}\nTrails:\n{% for trail in message.trails %}\n{{ trail }}\n{% endfor %}";
        file_put_contents($content."/".$request->id.".twig", $content_value.$content_values);
        return response('');
    }

    /**
     * Display the specified resource.
     *
     * @param  \July\Message\MessageForm  $form
     * @return \Illuminate\Http\Response
     */
    public function show(MessageForm $form)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \July\Message\MessageForm  $form
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, MessageForm $form)
    {
        // 更新类型
        $form->update($request->all());

        return response('');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \July\Message\MessageForm  $form
     * @return \Illuminate\Http\Response
     */
    public function destroy(MessageForm $form)
    {
        $form->delete();

        return response('');
    }

    /**
     * 检查主键是否重复
     *
     * @param  string|int  $id
     * @return \Illuminate\Http\Response
     */
    public function exists($id)
    {
        return response([
            'exists' => !empty(MessageForm::find($id)),
        ]);
    }
}
