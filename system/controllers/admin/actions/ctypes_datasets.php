<?php

class actionAdminCtypesDatasets extends cmsAction {

    public function run($ctype_id){

        if (!$ctype_id) { cmsCore::error404(); }

        $ctype = $this->model_content->getContentType($ctype_id);
        if (!$ctype) { cmsCore::error404(); }

        $grid = $this->loadDataGrid('ctype_datasets');

        if ($this->request->isAjax()) {

            $this->model_content->orderBy('ordering', 'asc');

            $datasets = $this->model_content->getContentDatasets($ctype_id);

            $this->cms_template->renderGridRowsJSON($grid, $datasets);

            $this->halt();

        }

        return $this->cms_template->render('ctypes_datasets', array(
            'ctype' => $ctype,
            'grid'  => $grid
        ));

    }

}
