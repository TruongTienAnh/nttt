<?php
namespace App\Controllers;

class ConfigController
{
    protected $app;

    public function __construct()
    {
        $this->app = app();
    }

    // =========================================================
    // BRANCHES (Chi nhánh)
    // =========================================================

    public function Brands()
    {
        $branches = app()->db->select('branches', '*', [
            'deleted' => 0,
            'ORDER'   => ['name' => 'ASC'],
        ]);
    
        return view('config/brands', [
            'brands' => $branches,
        ]);
    }
    
    public function BrandStore()
    {
        $validator = app()->validate(
            [
                'name' => 'required',
            ],
            [
                'name.required' => 'Tên chi nhánh không được để trống',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'alert'  => $validator->first(),
            ], 400);
        }

        $name            = app()->xss->clean(request('name'));
        $address         = app()->xss->clean(request('address') ?? '');
        $phone           = app()->xss->clean(request('phone') ?? '');
        $type            = in_array(request('type'), ['spa', 'retail', 'hybrid']) ? request('type') : 'spa';
        $organization_id = (int) request('organization_id') ?? 0;
        $is_active       = (int) request('is_active') === 1 ? 1 : 0;

        // Kiểm tra trùng tên
        $exists = app()->db->get('branches', 'id', [
            'name'    => $name,
            'deleted' => 0,
        ]);

        if ($exists) {
            return response()->json([
                'status' => 'error',
                'alert'  => 'Tên chi nhánh đã tồn tại',
            ], 400);
        }

        app()->db->insert('branches', [
            'organization_id' => $organization_id,
            'name'            => $name,
            'address'         => $address,
            'phone'           => $phone,
            'type'            => $type,
            'is_active'       => $is_active,
            'deleted'         => 0,
            'created_at'      => date('Y-m-d H:i:s'),
            'updated_at'      => date('Y-m-d H:i:s'),
        ]);

        $newId = app()->db->id();

        return response()->json([
            'status' => 'success',
            'alert'  => 'Thêm chi nhánh thành công',
            'id'     => $newId,
        ]);
    }

    public function BrandEdit()
    {
        $id = app()->request->params('id');

        $branch = app()->db->get('branches', '*', [
            'id'      => $id,
            'deleted' => 0,
        ]);

        if (!$branch) {
            return response()->json([
                'status' => 'error',
                'alert'  => 'Không tìm thấy chi nhánh',
            ], 404);
        }

        if (app()->request->isHtmx()) {
            return view('config/brands-post', [
                'brand' => (object) $branch,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'data'   => $branch,
        ]);
    }

    public function BrandUpdate()
    {
        $id = app()->request->params('id');

        $branch = app()->db->get('branches', 'id', [
            'id'      => $id,
            'deleted' => 0,
        ]);

        if (!$branch) {
            return response()->json([
                'status' => 'error',
                'alert'  => 'Không tìm thấy chi nhánh',
            ], 404);
        }

        $validator = app()->validate(
            [
                'name' => 'required',
            ],
            [
                'name.required' => 'Tên chi nhánh không được để trống',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'alert'  => $validator->first(),
            ], 400);
        }

        $name = app()->xss->clean(request('name'));

        // Kiểm tra trùng tên với branch khác
        $exists = app()->db->get('branches', 'id', [
            'name'    => $name,
            'id[!]'   => $id,
            'deleted' => 0,
        ]);

        if ($exists) {
            return response()->json([
                'status' => 'error',
                'alert'  => 'Tên chi nhánh đã được sử dụng',
            ], 400);
        }

        app()->db->update('branches', [
            'name'       => $name,
            'address'    => app()->xss->clean(request('address') ?? ''),
            'phone'      => app()->xss->clean(request('phone') ?? ''),
            'type'       => in_array(request('type'), ['spa', 'retail', 'hybrid']) ? request('type') : 'spa',
            'is_active'  => (int) request('is_active') === 1 ? 1 : 0,
            'updated_at' => date('Y-m-d H:i:s'),
        ], ['id' => $id]);

        return response()->json([
            'status' => 'success',
            'alert'  => 'Cập nhật chi nhánh thành công',
        ]);
    }

    public function BrandDelete()
    {
        $id = app()->request->params('id');

        $branch = app()->db->get('branches', 'id', [
            'id'      => $id,
            'deleted' => 0,
        ]);

        if (!$branch) {
            return response()->json([
                'status' => 'error',
                'alert'  => 'Không tìm thấy chi nhánh hoặc đã bị xóa',
            ], 404);
        }

        app()->db->update('branches', [
            'deleted'    => 1,
            'updated_at' => date('Y-m-d H:i:s'),
        ], ['id' => $id]);

        return response()->json([
            'status' => 'success',
            'alert'  => 'Đã xóa chi nhánh',
        ]);
    }

    public function BrandRestore()
    {
        $id = app()->request->params('id');

        $branch = app()->db->get('branches', 'id', [
            'id'      => $id,
            'deleted' => 1,
        ]);

        if (!$branch) {
            return response()->json([
                'status' => 'error',
                'alert'  => 'Không tìm thấy chi nhánh hoặc chưa bị xóa',
            ], 404);
        }

        app()->db->update('branches', [
            'deleted'    => 0,
            'updated_at' => date('Y-m-d H:i:s'),
        ], ['id' => $id]);

        return response()->json([
            'status' => 'success',
            'alert'  => 'Khôi phục chi nhánh thành công',
        ]);
    }

    public function BrandToggleStatus()
    {
        $id = app()->request->params('id');

        $branch = app()->db->get('branches', ['id', 'is_active'], [
            'id'      => $id,
            'deleted' => 0,
        ]);

        if (!$branch) {
            return response()->json([
                'status' => 'error',
                'alert'  => 'Không tìm thấy chi nhánh',
            ], 404);
        }

        $newStatus = $branch['is_active'] == 1 ? 0 : 1;

        app()->db->update('branches', [
            'is_active'  => $newStatus,
            'updated_at' => date('Y-m-d H:i:s'),
        ], ['id' => $id]);

        return response()->json([
            'status'     => 'success',
            'alert'      => $newStatus ? 'Đã kích hoạt chi nhánh' : 'Đã tắt chi nhánh',
            'new_status' => $newStatus,
        ]);
    }
}