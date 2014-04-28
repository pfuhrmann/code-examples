<?php

use Repositories\Device\DeviceRepositoryInterface;

class DeviceController extends BaseController {

    protected $device;

    public function __construct(DeviceRepositoryInterface $devices) {
        $this->device = $devices;
    }

    /**
     * Display all devices.
     *
     * @return Response
     */
    public function index()
    {
        //
    }

    /**
     * Store new device
     *
     * @return Response
     */
    public function store()
    {
        // Check if there is already same reg_id
        $device = $this->device->findByRegId(Input::get('registration_id'));
        if ($device) {
            // Only update as reg_id is already present
            $device = $this->device->update($device->id, Input::all());
        } else {
            // Creating new device
            $device = $this->device->create(Input::all());
        }

        if (!$device) {
            return JsonHelper::createErrorResponse(3, 'Record not inserted');
        } else {
            // Returning freshly created/updated device object
            return JsonHelper::createPayloadResponse($device->toArray());
        }
    }

    /**
     * Dispay specified device.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        $device = $this->device->find($id);
        
        if (!($device)) {
            return JsonHelper::createErrorResponse(5, 'No such record');
        } else {
            return JsonHelper::createPayloadResponse($device->toArray());
        }
    }

    /**
     * Update the device.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id)
    {
        // Get device
        $device = $this->device->find($id);

        if (!$device) {
            return JsonHelper::createErrorResponse(5, 'No such record');
        }

        $device = $this->device->update($id, Input::all());

        if (!$device) {
            return JsonHelper::createErrorResponse(3, 'Record not updated');
        }

        // Returning freshly edited device object
        return JsonHelper::createPayloadResponse($device->toArray());
    }

    /**
     * Remove the device from the storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        // Check if device exists
        $device = $this->device->find($id);

        if (!$device) {
            return JsonHelper::createErrorResponse(5, 'No such record');
        }

        if (!$this->device->delete($id)) {
            return JsonHelper::createErrorResponse(3, 'Record not deleted');
        } else {
            return JsonHelper::createEmptyResponse();
        }
    }

}