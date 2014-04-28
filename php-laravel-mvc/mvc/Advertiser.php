<?php
use \Illuminate\Database\Eloquent\Model as Eloquent;

class Advertiser extends Eloquent {

    public $timestamps = false;
    protected $table = 'uaccounts';
    protected $primaryKey = 'user_id';
    protected $visible = [
        'user_id','email_address','first_name', 'last_name', 'company_name',
        'sites', 
    ];

    public function campaign()
    {
        return $this->hasMany('Campaign', 'campaign_owner');
    }

    public function authorization()
    {
        return $this->hasMany('Authorization', 'advertiser_id');
    }

    public function creator()
    {
        return $this->belongsTo('Advertiser', 'creator_id');
    }

    public function manager()
    {
        return $this->belongsTo('Advertiser', 'manager_id');
    }

    public function group()
    {
        return $this->belongsTo('Group', 'account_type');
    }

    public function sites()
    {
        return $this->hasMany('Site', 'uaccount_id');
    }

    public function displayNotes()
    {
        if (!empty($this->notes)) {
            return $this->notes;
        } else {
            return 'N/A';
        }
    }

    public function displayTaxID($value)
    {
        if (!empty($value)) {
            return $value;
        } else {
            return 'N/A';
        }
    }

    /**
     * Get string representation of the Advertiser origin
     *
     * @return string Origin
     */
    public function getOriginStr()
    {
        switch ($this->origin) {
            case '0':
                return 'Admin';
                break;
            case '1':
                return 'Web';
                break;
            default:
                return 'N/A';
                break;
        }
    }

    /**
     * Generate simple random key
     * 
     * @param Int $length Length of the generated key
     * @return string
     */
    public static function generateKey($length) 
    {
        $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        return substr(str_shuffle($chars), 0, $length);
    }

    /**
     * Activate / Deactivate advertiser account
     * 
     * @param Int $id  Advertiser ID
     * @param Int $status  Status to change to
     * @param bool $campaigns  Deactivate associated campaigns
     */
    public static function deactivateActivate($id, $status, $campaigns = true) 
    {
        // First we will activate/deactivate account
        $advertiser = Advertiser::with('Campaign')->find($id);
        $advertiser->account_status = $status;
        $advertiser->save();

        if ($campaigns) {
            // Now we are going to activate/deactivate all campaigns
            foreach ($advertiser->campaign as $campaign) {
                $campaign->campaign_status = ($status) ? 2 : 3;
                $campaign->save();
            }
        }
    }

}
