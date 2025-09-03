<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Asset Authorization PDF</title> 
    <style>
        @page {
            margin: 3% 10% 10% 10%;
        }
        body {
            margin: 0;
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            line-height: 1.5;
        }

        .header {
            text-align: center;
            border-bottom: 1px solid #000;
            padding: 10px 0;
            margin: 0;              /* remove extra spacing */
        }

        .header img {
            width: 100px;
            height: auto;
            margin: 0;
            padding: 0;
        }

        .company-name {
            margin-top: 5px;      
        }
        h3 { 
            margin: 15px 0 5px; 
            text-transform: uppercase; 
        }
        .section { margin-top: 20px; }
        .footer {
            margin-top: 40px;
            text-align: center;
        }
        .footer p {
            margin: 3px 0;
        } 
        .asset-details {
            display: flex;
            gap: 40px;  
        }
        .asset-details > div {
            flex: 1; 
        }
        .asset-details p {
            margin: 5px 0;  
        } 
        .authorization-id {
            margin-top: 50px;
            text-align: right;
            font-weight: bold;
            text-transform: uppercase;
        }
       .acknowledgement {
            display: flex;
            justify-content: space-between;
            gap: 60px;
            margin-top: 15px;
        }

        .ack-left {
            flex: 1;
        }

        .ack-right {
            flex: 3;
            text-align: justify;
        }
        p{
            font-size: 11px;
        }
    </style>
</head>
<body> 
    <div class="header">
        <img src="{{ public_path('build/img/theos.png') }}" alt="Logo" style="width:100px; height:auto;"> 
        <br>
        <strong>Theos Helios Security Agency Corporation</strong>
        <div style="text-align:right;margin-top:20px;">
        <strong style="font-size:16px;">  ASSET MOVEMENT </strong>
        <br>
         <span>ASS-{{ explode('-', $asset->assets->name)[1] }}-{{ explode('-', $asset->assets->name)[2] }}</span> 
        </div>

    </div>  
    <h3>Employee and Item / Equipment Details</h3>
    <div class="asset-details">
        <div>
            <p><strong>Asset Name:</strong> {{ $asset->assets->name }}</p>
            <p><strong>Item Name:</strong> {{ $asset->assets->item_name }}</p>
            <p><strong>Asset Serial No.:</strong> {{ $asset->assets->serial_number }}</p>
            <p><strong>Asset Category:</strong> {{ $asset->assets->category->name }}</p>
             <p><strong>Remarks:</strong> {{ $asset->assets->description }}</p>
            <p><strong>Purchase Date:</strong> {{ $asset->assets->purchase_date ?? '' }}</p>
            <p><strong>Gross Purchase Amount:</strong> {{ number_format($asset->assets->price, 2) }}</p>
        </div>
        <div>
            <p><strong>Location:</strong> {{ $user->branch->name ?? '' }}</p>
            <p><strong>Custodian:</strong> {{ $user->designation->designation_name ?? '' }}</p>
            <p><strong>Department:</strong> {{ $user->department->department_name ?? '' }}</p>
        </div>
    </div> 
    <hr>
    <div class="section">
        <h3> <strong> REPLACEMENT OR REPAIR POLICY</strong></h3> 
        <p> <i style="font-weight:bold;"> REPLACEMENT OR REPAIR  </i> 
            <br>
            1. <strong>Replacement or Repair </strong> - The purchasing of replacement or repairing of the asset shall be done by the company's Supply Chain Management
            Department, this is to ensure it is from the accredited vendor and service provider. 
            <br>
            2.  <strong>Payment</strong> - May be done thru SALARY DEDUCTION or may be paid in full thru bank transfer or cash payment only,
            <br >
            3. <strong>Time frame for replacing or repairing</strong> - replacement or repair process should be done and finalized within Thirty (30) days
        </p>
    </div>
    <hr>
    
    <div class="section">
        <h3>ACKNOWLEDGEMENT</h3>
        <div class="acknowledgement">
            <div class="ack-left">
                <p><strong>Checked and Issued By :</strong> _______________</p>
                <p><strong>Approved By :</strong> _______________</p>
            </div>
            <div class="ack-right">
                <p>
                    I have Received the item/equipment in good working condition and by
                    affixing my signature I am acknowledging the responsibility and duty to
                    preserve and maintain the good working condition of this equipment /
                    item / tool and that any damage / loss on the company's property I shall
                    replace and pay for the said equipment:
                </p>
                <p><strong>Received by :</strong> _______________</p>
            </div>
        </div>
    </div>
 
</body>  
</html>
