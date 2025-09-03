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
        <strong style="font-size:16px;">   MOTORCYCLE AUTHORIZATION </strong>
        <br>
         <span>ASS-{{ explode('-', $asset->assets->name)[1] }}-{{ explode('-', $asset->assets->name)[2] }}</span> 
        </div>

    </div>
     
    <p><strong>Issuance date:</strong> {{ now()->format('m-d-Y') }}</p>

    <p>
        Theos Helios Security Agency Corporation (“Corporation”) be, as it is hereby,
        authorized THE CORPORATION’S PERSONNEL (“{{$user->designation->designation_name ?? ''}}”), to use the
        {{ strtoupper($asset->assets->category->name ?? 'EQUIPMENT') }} bearing the following details:
    </p>
    <hr>
    <h3>ASSET DETAILS</h3>
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
            <p><strong>Rider:</strong> {{ $user->designation->designation_name ?? '' }}</p>
            <p><strong>Department:</strong> {{ $user->department->department_name ?? '' }}</p>
        </div>
    </div> 
    <hr>
    <div class="section">
        <h3>AUTHORIZATION</h3>
        <p>
            That hereby RESOLVED , that the RIDER is qualified and is holding a VALID DRIVER’s LICENSE,
            and this {{ strtolower($asset->assets->category->name ?? 'equipment') }} will be used by the RIDER for the
            CORPORATION’s OFFICIAL BUSINESS only, other than the said purpose shall be unauthorized and that
            only the name mentioned above shall be authorized to use this {{ strtolower($asset->assets->category->name ?? 'equipment') }}
            and this authorization is non-transferable. Further RESOLVED that the RIDER shall strictly monitor
            and fulfill the preventive and regular maintenance of the {{ strtoupper($asset->assets->category->name ?? 'equipment') }}.
            The above resolutions are subsisting, in full force and effect and have not been superseded,
            amended, cancelled or revoked as of this date.
        </p>
    </div>
    <hr>
    <div class="section">
        <h3>VALIDITY</h3>
        <p>
            That only the RIDER employed by the Corporation shall use this 
            {{ strtolower($asset->assets->category->name ?? 'equipment') }} and this authorization 
            may only be valid for one year from date of issuance.
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
                    I have Received the item/equipment in good working condition and by affixing my signature 
                    I am acknowledging the responsibility and duty to preserve and maintain the good working 
                    condition of this equipment / item / tool and that any damage / loss on the company's property 
                    I shall replace and pay for the said equipment:
                </p>
                <p><strong>Received by :</strong> _______________</p>
            </div>
        </div>
    </div>
 
</body>  
</html>
