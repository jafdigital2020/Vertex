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
            margin: 0; 
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
        <strong style="font-size:16px;">  POLICY AND AUTHORIZATION OF COMPANY ISSUED
        EQUIPMENT REV. 01 </strong>
        <br>
         <span>ASS-{{ explode('-', $asset->assets->name)[1] }}-{{ explode('-', $asset->assets->name)[2] }}</span> 
        </div> 
    </div> 
        <p>
            This policy about cellular phone usage applies to any device that makes or receives phone calls, leaves messages, sends text messages, surfs the
    internet, or downloads and allows for the reading of and responding to email whether the device is company-supplied or personally owned. The
    company issued the mobile phone to the employees to have immediate access to clients / suppliers / collegues and may easily be contacted   
        </p>
    <hr>
    <div class="section">
        <h3>HOW THE CELLULAR PHONE IS EXPECTED TO BE USED BY EMPLOYEES:</h3>
        <ol>
            <li><strong>Official business purposes only</strong>, and employees must preserve the device in good working condition.</li>
            <li><strong>Official business use of internet, calls, and text messages</strong> (with colleagues, clients, and security personnel) is permitted.</li>
            <li>Employees must turn off or silence their phones when asked.</li>
            <li>Employees who leave or resign from the company for any reason must return the issued mobile/cellular phone to the Supply Chain Management Department.</li>
        </ol>
    </div> 
    <div class="section"> 
      <p>
        <strong><i>AUTHORIZED APPS ON COMPANY-ISSUED MOBILE PHONE</i></strong>
        <ol >
            <li><b>Discord</b> - <i>Official messaging platform of the company</i></li>
            <li><b>Viber</b> - <i>Coordination with clients using this messaging platform</i></li>
            <li><b>CamScanner</b> -<i>Scanning documents using the mobile phone's camera</i></li>
            <li><b>Gmail</b> - <i>Emailing clients, vendors, colleagues, and for other official business purposes</i></li>
        </ol>
    </p> 
    </div>
    <hr>
    <div class="section">
        <h3>WHAT IS NOT ALLOWED</h3>
        <ol>
            <li>Playing games on company-issued and/or personal mobile phones during work hours.</li>
            <li>Downloading games and/or social media applications (not approved by management) on company-issued cellular phones.</li>
            <li>Using the mobile phone's camera or microphone to record confidential information.</li>
            <li>Uploading or saving obscene material on the company phone’s storage or using the company internet for such content.</li>
        </ol>

        <p><strong><i>MOBILE PHONE RESTRICTIONS WHILE DRIVING</i></strong></p>
        <p>
            The following are strictly prohibited while driving: receiving or placing calls, text messaging, surfing the internet, 
            checking or responding to emails, or using the phone for any other purpose related to employment, business, clients, 
            vendors, meetings, or other company-related responsibilities.  
            <br><br>
            This also applies to any company or personally related activities not specifically mentioned here 
            <i>while driving</i>.
        </p>
    </div>
    <hr>
<div class="section">
    <h3>OTHER RESPONSIBILITIES OF THE EMPLOYEE ON THE COMPANY-ISSUED CELLULAR PHONE</h3>
    <p>
        The employee is the user of the issued cellular phone and shall be responsible for its proper care, maintenance, and 
        protection against loss, theft, damage, and/or irreparable issues.  
        <br><br>
        In case the cellular phone incurs damage beyond repair, or is lost or stolen, the employee shall bear, assume, and pay 
        the expenses for replacing the issued company phone along with its accessories (screen protector, phone case, charging 
        cube, charging cable, earphones, etc.), purchased apps approved by the company, and all related costs in producing the 
        cellular phone.
    </p>

    <p><strong><i>REPLACEMENT OR REPAIR</i></strong></p>
    <ol>
        <li><strong>Replacement or Repair</strong> – <i>The purchasing of replacement or repair of the asset shall be done by the company’s Supply Chain Management Department through accredited vendors and service providers.</i></li>
        <li><strong>Payment</strong> – <i>Expenses may be covered through salary deduction or may be paid in full via bank transfer or cash payment.</i></li>
        <li><strong>Time Frame for Replacing or Repairing</strong> – <i>The replacement or repair process must be completed within thirty (30) days.</i></li>
    </ol>
</div>
    <hr>
<div class="section">
        <h3>ASSET DETAILS</h3>
    <table style="width:100%; border-collapse:collapse; font-size:12px; margin-top:10px;">
        
        <tr>
            <td style="width:50%; vertical-align:top; padding:10px;">
                <p style="margin:6px 0;"><strong>Asset Name:</strong> {{ $asset->assets->name }}</p>
                <p style="margin:6px 0;"><strong>Item Name:</strong> {{ $asset->assets->item_name }}</p>
                <p style="margin:6px 0;"><strong>Asset Serial No.:</strong> {{ $asset->assets->serial_number }}</p>
                <p style="margin:6px 0;"><strong>Asset Category:</strong> {{ $asset->assets->category->name }}</p>
                <p style="margin:6px 0;"><strong>Remarks:</strong> {{ $asset->assets->description }}</p>
                <p style="margin:6px 0;"><strong>Purchase Date:</strong> {{ $asset->assets->purchase_date ?? '' }}</p>
                <p style="margin:6px 0;"><strong>Gross Purchase Amount:</strong> {{ number_format($asset->assets->price, 2) }}</p>
                
                <br><br><br>
                <p style="margin:6px 0;"><strong>Checked and Issued By :</strong> _______________</p>
                <p style="margin:6px 0;"><strong>Approved By :</strong> _______________</p>
            </td>
            <td style="width:50%; vertical-align:top; padding:10px;">
                <p style="margin:6px 0;"><strong>Location:</strong> {{ $user->branch->name ?? '' }}</p>
                <p style="margin:6px 0;"><strong>Rider:</strong> {{ $user->designation->designation_name ?? '' }}</p>
                <p style="margin:6px 0;"><strong>Department:</strong> {{ $user->department->department_name ?? '' }}</p>
                
                <br>
                <p style="margin:6px 0; text-align:justify;">
                    I have received the item/equipment in good working condition and by affixing my signature 
                    I acknowledge the responsibility and duty to preserve and maintain the good working 
                    condition of this equipment/item/tool. Any damage or loss of the company's property 
                    shall be replaced and paid for by me within 30 days.
                </p>
                
                <br>
                <p style="margin:6px 0;"><strong>Received by :</strong> _______________</p>
                <p style="margin:6px 0;"><strong>Date Signed :</strong> _______________</p>
            </td>
        </tr>
    </table> 
</div>
</div>
 
</body>  
</html>
