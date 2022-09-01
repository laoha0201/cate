<?php

namespace Laoha\Cate\Actions;

use Dcat\Admin\Tree\RowAction;

class Show extends RowAction
{
    /**
     * @return array|null|string
     */
    public function title()
    {
        return '<i class="feather icon-eye" title="' . __('admin.detail') . '"></i> &nbsp;&nbsp;';
    }


    /**
     * @return string
     */
    public function href()
    {
        return admin_url("{$this->resource()}/{$this->getKey()}");
        //$this->parent->urlWithConstraints("{$this->resource()}/{$this->getKey()}");
    }
}
