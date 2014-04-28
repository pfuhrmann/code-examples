<?php  namespace Repositories\Device;

use Device;

class DbDeviceRepository implements DeviceRepositoryInterface {

    private $device;

    public function __construct(Device $device) {
        return $this->device = $device;
    }

    public function find($id)
    {
        return $this->device->find($id);
    }

    public function findByRegId($reg_id)
    {
        return $this->device->where('registration_id', $reg_id)
            ->first();
    }

    public function create($data)
    {
        $device = $this->device;
        $device->registration_id = $data['registration_id'];
        $device->version = $data['version'];
        $device->save();

        return $device;
    }

    public function update($id, $data) 
    {
        $device = $this->device->find($id);
        $device->registration_id = $data['registration_id'];
        $device->version = $data['version'];
        $device->save();

        return $device;
    }

    public function delete($id)
    {
        $device = $this->device->find($id);
        return $device->delete();
    }

}