<?php  namespace Repositories\Device;

interface DeviceRepositoryInterface {
    public function find($id);
    public function findByRegId($reg_id);
    public function create($data);
    public function update($id, $data);
    public function delete($id);
}