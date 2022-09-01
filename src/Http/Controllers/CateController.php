<?php

namespace Laoha\Cate\Http\Controllers;

use Laoha\Cate\Models\Cate;
use Laoha\Cate\Http\Actions\Show as ActShow;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Form;
use Dcat\Admin\Show;
use Dcat\Admin\Tree;
use Dcat\Admin\Layout\Content;

/**
 * 分类管理
 */
class CateController extends AdminController
{
    public $title = "";
    public $base = "";

    /**
     * Get title.
     * @return string
     */
    public function title(): string
    {
        return $this->title . '分类';
    }

    /**
     * Index interface.
     *
     * @param  Content  $content
     * @return Content
     */
    public function index(Content $content)
    {
        
		if (empty($this->base)){
			return "未指定base参数";
		}
		return $content
            ->translation($this->translation())
            ->title($this->title())
            ->description($this->description()['index'] ?? trans('admin.list'))
            ->body($this->grid());
    }

    /**
     * @return \Dcat\Admin\Tree
     */
    protected function grid()
    {
        return new Tree(new Cate($this->base), function (Tree $tree) {
            //$tree->disableCreateButton();
            $tree->disableQuickCreateButton();
            $tree->disableEditButton();
            $tree->maxDepth(3);

            $tree->actions(function (Tree\Actions $actions) {
                $actions->append(new ActShow());
                if ($actions->getRow()->extension) {
                    $actions->disableDelete();
                }
            });
            $tree->branch(function ($branch) {
                $allow_comment = $branch['allow_comment'] ? '<span class="badge alert-danger">评</span> ' : '';
                if ($branch['allow_publish']) {
                    $payload = "&nbsp;<strong>{$branch['name']}</strong>&nbsp;&nbsp;&nbsp;&nbsp;{$branch['slug']}&nbsp;&nbsp;&nbsp;&nbsp;" . $allow_comment;
                } else {
                    $payload = "&nbsp;<strong class='text-muted'>{$branch['name']}</strong>&nbsp;&nbsp;&nbsp;&nbsp;{$branch['slug']}";
                }
                return $payload;
            });
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    public function form(): Form
    {
        $base = $this->base;
        return Form::make(new Cate($base), function (Form $form) use ($base) {
            $form->width(9, 3);

            if ($form->isCreating()) {
                $form->hidden('base')->default($base);
                $form->select('parent_id', '父级')->options(Cate::selectBaseOptions($base))->default(0);
            } else {
                $form->display('id', 'ID');
            }
            //$form->html(url()->current());
            $form->text('name', '分类名称')->required()->rules('string');

			if ($form->isCreating()) {
				$form->text('slug', '分类标识')->required()->help('必须是字母数字或划线,且唯一')->rules('unique:cate,slug,null,id,base,'.$base.'|regex:/^[a-zA-Z0-9_-]+$/',['unique'=>'分类标识已经存在','regex'=>'分类标识中包含非法字符']);			
			}else{
				$id = $form->model()->id;
				$form->text('slug', '分类标识')->required()->help('必须是字母数字或划线,且唯一')->rules('unique:cate,slug,'.$id.',id,base,'.$base.'|regex:/^[a-zA-Z0-9_-]+$/',['unique'=>'分类标识已经存在','regex'=>'分类标识中包含非法字符']);	
			}
            $form->text('groups', '分组设置')->help('用逗号分隔');
            $form->image('thumb', '分类图片')->rules('file|image')->retainable()->autoUpload();
            $form->switch('allow_publish', '允许发布')->default(1);
            $form->switch('allow_comment', '允许评论')->default(0);
			
            $form->text('desc', '分类说明');
            $this->extend($form);
            $form->footer(function ($footer) {
                // 去掉`查看`checkbox
                $footer->disableViewCheck();

                // 去掉`继续编辑`checkbox
                $footer->disableEditingCheck();

                // 去掉`继续创建`checkbox
                $footer->disableCreatingCheck();
            });
        });
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     *
     * @return Show
     */
    protected function detail($id): Show
    {
        return Show::make($id, new Cate($this->base), function (Show $show) {
            $show->panel()
                ->tools(function ($tools) {
                    $tools->disableEdit();
                    $tools->disableDelete();
                    // 显示快捷编辑按钮
                    $tools->showQuickEdit();
                });

            $show->field('id', 'ID');
            $show->field('name', '分类名称');
            $show->field('slug', '分类标识');
            $show->field('allow_publish', '允许发布')->using(['否', '是']);
            $show->field('allow_comment', '允许评论')->using(['否', '是']);
            $show->field('thumb', '分类图片')->image();
        });
    }

    protected function extend($form)
    {
        //$form->keyValue('ext', '扩展参数');
    }
}
