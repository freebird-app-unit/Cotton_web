<?php
namespace App\Helper;
use App\Models\Sellers;
use App\Models\Buyers;

class CommonHelper
{
    public static function check_user_amount($user_id, $user_type)
    {
        $response = [
            'success' => true,
            'data' => []
        ];
        if ($user_type == 'seller') {
            $seller_details = Sellers::with('user_plan')->where('id', $user_id)->first();

            $response['data'] = $seller_details;

            if ($seller_details->wallet_amount < 1) {
                $response['message'] = 'You have no sufficient balance into wallet!';
                $response['success'] = false;
                return $response;
            }

            if (empty($seller_details->user_plan)) {
                $response['message'] = 'You have no active plan!222';
                $response['success'] = false;
                return $response;
            }

            if ($seller_details->user_plan->status == 0) {
                $response['message'] = 'You have no active plan!000';
                $response['success'] = false;
                return $response;
            }

            if (strtotime($seller_details->user_plan->expiry_date) < strtotime(date('Y-m-d'))) {
                $response['message'] = 'Your plan has been expired!';
                $response['success'] = false;
                return $response;
            }
        } else if ($user_type == 'buyer') {
            $buyer_details = Buyers::with('user_plan')->where('id', $user_id)->first();

            $response['data'] = $buyer_details;
            if ($buyer_details->wallet_amount < 1) {
                $response['message'] = 'You have no sufficient balance into wallet!';
                $response['success'] = false;
                return $response;
            }

            if (empty($buyer_details->user_plan)) {
                $response['message'] = 'You have no active plan!';
                $response['success'] = false;
                return $response;
            }

            if ($buyer_details->user_plan->status == 0) {
                $response['message'] = 'You have no active plan!';
                $response['success'] = false;
                return $response;
            }

            if (strtotime($buyer_details->user_plan->expiry_date) < strtotime(date('Y-m-d'))) {
                $response['message'] = 'Your plan has been expired!';
                $response['success'] = false;
                return $response;
            }
        }

        return $response;
    }
}
