<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"/></head>
<body>
<table>
    <tr>
        <td><img height="120" src="{{ $broker_header_image }}"></td>
        <td>
            <strong style="font-size: 56px;">{{ $broker_name }}</strong><br/>
            <span>{{ $broker_address }}</span><br/>
            <span>{{ $broker_state }} , {{ $broker_country }} | Phone : {{ $broker_mobile_number }} | Fax : {{ $broker_mobile_number_2}}</span><br/>
            <strong>Email : {{ $broker_email }} | Web : {{ $broker_url }}</strong><br/>
            <strong>{{ $broker_name }}</strong>
        </td>
    </tr>
</table>
<table width="100%">
    <tr>
        <td colspan="4" style="text-align: center;"></td>
    </tr>
    <tr>
        <td colspan="4" style="text-align: center;border-top:1.5px solid #000;border-bottom:1.5px solid #000;font-size:14px;font-weight:bold;padding:5px">SALE - PURCHASE INFORMATION MEMO</td>
    </tr>
    <tr>
        <td colspan="1"><b>DATE</b></td>
        <td>: {{ $deal_date }}</td>
    </tr>
    <tr>
        <td><b style="font-size: 14px;">BUYER</b></td>
        <td>: {{ $buyer_name_address }}</td>
        <td><b style="font-size: 14px;">STATION</b></td>
        <td>: {{ $buyer_station }}</td>
    </tr>
    <tr>
        <td><b style="font-size: 14px;">SELLER</b></td>
        <td>: {{ $seller_name_address }}</td>
        <td><b style="font-size: 14px;">STATION</b></td>
        <td>: {{ $seller_station }}</td>
    </tr>
</table>
<p style="font-size: 14px;">Dear Sir,</p>
<p style="font-size: 14px;">Here we inform you that the quality and quantity of cottons sales of purchase by you are as per below details :
</p>
<table width="100%" style="border:1px solid #000;">
    <tr>
        <td style="border-bottom: 1px solid #000;font-size: 14px;"><b>REF. NO.</b></td>
        <td style="border-bottom: 1px solid #000;font-size: 14px;">: <b>{{ $ref_no }}</b></td>
    </tr>
    <tr>
        <td style="border-bottom: 1px solid #000;font-size: 14px;"><b>QUALITY</b></td>
        <td style="border-bottom: 1px solid #000;font-size: 14px;">: {{ $product_name_pdf }}</td>
    </tr>
    <tr>
        <td style="border-bottom: 1px solid #000;font-size: 14px;"><b>PARAMETER</b></td>
        <td style="border-bottom: 1px solid #000;font-size: 14px;">: {{ $attribute_array_pdf }}</td>             
    </tr>
    <tr>
        <td style="border-bottom: 1px solid #000;font-size: 14px;"><b>RATE/PER CANDY</b></td>
        <td style="border-bottom: 1px solid #000;font-size: 14px;">: {{ $deal_price }}</td>
    </tr>
    <tr>
        <td style="border-bottom: 1px solid #000;font-size: 14px;"><b>BALES</b></td>
        <td style="border-bottom: 1px solid #000;font-size: 14px;">: {{ $deal_no_of_bales }}</td>
    </tr>
    <tr>
        <td style="border-bottom: 1px solid #000;font-size: 14px;"><b>DELIVERY TIME</b></td>
        <td style="border-bottom: 1px solid #000;font-size: 14px;">: REGULAR</td>
    </tr>
    <tr>
        <td style="border-bottom: 1px solid #000;font-size: 14px;"><b>SODA TYPE</b></td>
        <td style="border-bottom: 1px solid #000;font-size: 14px;">: DIRECT DISPATCH</td>
    </tr>
    <tr>
        <td style="border-bottom: 1px solid #000;font-size: 14px;"><b>STATION</b></td>
        <td style="border-bottom: 1px solid #000;font-size: 14px;">: {{ $buyer_station }}</td>
    </tr>
    <tr>
        <td style="border-bottom: 1px solid #000;font-size: 14px;"><b>PAYMENT CONDITION</b></td>
        <td style="border-bottom: 1px solid #000;font-size: 14px;">: 15 DAYS FROM THE DATE OF DISPATCH</td>
    </tr>
    <tr>
        <td style="font-size: 14px;"><b>OTHER CONDITION</b></td>
        <td style="font-size: 14px;">: 1). Gujarat Dharo</td>
    </tr>
</table>
<p style="font-size: 14px;line-height:1.2;margin: 5px 0 5px;">(1) We have prepared this contract confirmation as per details & terms mentioned here agreed by buyer & seller both, if any discrepancy in contract details & terms. Buyer & sellers have to inform us with the please.</p>
<p style="font-size: 14px;line-height:1.2;margin: 5px 0 5px;">(2) Buyer & seller both has to sign the contract confirmation & have to send us within 2 days.</p>
<p style="font-size: 14px;line-height:1.2;margin: 5px 0 5px;">(3) We send this contract confirmation by FAX / E-MAIL & if we will not get any reply from buyer or seller even though this contract confirmation is treat as confirm & binding to buyer & seller both.</p>
<p style="font-size: 14px;line-height:1.2;margin: 5px 0 5px;">(4) This contracts is prepared under terms &norms of Gujarat Cotton Association/Cotton Association of INDIA – Mumbai & if any dispute arise, we all have to follow the these norms as the FINAL NORMS.</p>
<p style="font-size: 14px;line-height:1.2;margin: 5px 0 5px;">(5) We are only brokers & we don’t have any legal nor financial responsibility if any disputes between buyer & seller.</p>
<p style="font-size: 14px;line-height:1.2;margin: 5px 0 5px;">(6) If any disputes arise then we are responsible to reply/talk to the same person who has acted as the representative of the buyer & seller. <!-- {{public_path('public/assets/images/gujarati.png')}} --></p>
<p style="font-size: 14px;line-height:1.2;margin: 5px 0 5px;"><!-- <img height="120" alt="{{asset('public/assets/images/gujarati.png')}}" src="data:image/png;base64,/{{base64_encode(asset('public/assets/images/gujarati.png'))}}"> --></p>

 <table  width="100%">
    <tr>
        <td style="border-top:1.5px solid #000"></td>
    </tr>
</table>
<table>
    <thead>
        <tr>
            <th style="width:150px; text-align:center;padding:20px">BROKER SIGN & STAMP</th>
            <th style="width:150px; text-align:center;padding:20px">BUYER SIGN & STAMP</th>
            <th style="width:150px; text-align:center;padding:20px">SELLER SIGN & STAMP</th>
        </tr>
    </thead>
    <tbody>
    <tr>
        <td style="width:150px; text-align:center;padding:20px"><img height="120" src="{{ $broker_stamp_image }}"></td>
        <td style="width:150px; text-align:center;padding:20px"><img height="120" src="{{ $buyer_stamp_image }}"></td>
        <td style="width:150px; text-align:center;padding:20px"><img height="120" src="{{ $seller_stamp_image }}"></td>
    </tr>  
    </tbody> 
</table>
</body>
</html>
