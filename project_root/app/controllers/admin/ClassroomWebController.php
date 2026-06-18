<?php

namespace App\Controllers\Admin;



use Core\WebController;

use App\Models\classmodel;



class ClassroomWebController extends WebController {

    private $model;



    public function __construct() {

        $this->model = new classmodel();

    }



    public function index() {

        $this->render('admin/classrooms/index', [

            'title' => 'Phòng học',

            'portal' => 'admin',

            'pageScripts' => ['classrooms-ajax.js'],

        ]);

    }



    public function create() {

        $this->render('admin/classrooms/form', [

            'title' => 'Thêm phòng học',

            'portal' => 'admin',

            'classroom' => null,

        ]);

    }



    public function store() {

        $this->requirePost();

        $result = $this->model->createClassroom($_POST);

        if (isset($result['error'])) {

            $this->setOld($_POST);

            $this->flash('error', $result['error']);

            $this->redirect('admin/classrooms/create');

        }

        $this->audit('CREATE_CLASSROOM', 'classrooms', $result['id'] ?? null);

        $this->flash('success', 'Đã tạo phòng học');

        $this->redirect('admin/classrooms');

    }



    public function edit($params) {

        $id = (int)($params['id'] ?? 0);

        $classroom = $this->model->getClassroomById($id);

        if (!$classroom) {

            $this->flash('error', 'Không tìm thấy phòng học');

            $this->redirect('admin/classrooms');

        }

        $this->render('admin/classrooms/form', [

            'title' => 'Sửa phòng học',

            'portal' => 'admin',

            'classroom' => $classroom,

        ]);

    }



    public function update($params) {

        $this->requirePost();

        $id = (int)($params['id'] ?? 0);

        $result = $this->model->updateClassroom($id, $_POST);

        if (isset($result['error'])) {

            $this->setOld($_POST);

            $this->flash('error', $result['error']);

            $this->redirect('admin/classrooms/' . $id . '/edit');

        }

        $this->audit('UPDATE_CLASSROOM', 'classrooms', $id);

        $this->flash('success', 'Đã cập nhật phòng học');

        $this->redirect('admin/classrooms');

    }



    public function delete($params) {

        $this->requirePost();

        $id = (int)($params['id'] ?? 0);

        $result = $this->model->deleteClassroom($id);

        if (isset($result['error'])) {

            $this->flash('error', $result['error']);

        } else {

            $this->audit('DELETE_CLASSROOM', 'classrooms', $id);

            $this->flash('success', 'Đã xóa phòng học');

        }

        $this->redirect('admin/classrooms');

    }

}


