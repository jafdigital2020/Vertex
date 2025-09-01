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
    <ol style="padding-left: 0;">
        <li>1.<strong>Official business purposes only</strong>, and employees must preserve the device in good working condition.</li>
        <li>2.<strong>Official business use of internet, calls, and text messages</strong> (with colleagues, clients, and security personnel) is permitted.</li>
        <li>3.Employees must turn off or silence their phones when asked.</li>
        <li>4.Employees who leave or resign from the company for any reason must return the issued mobile/cellular phone to the Supply Chain Management Department.</li>
    </ol>
</div> 
    <div class="section"> 
      <p>
        <strong><i>AUTHORIZED APPS ON COMPANY-ISSUED MOBILE PHONE</i></strong>
        <ol style="padding-left: 0;" >
            <li><b>1.Discord</b> - <i>Official messaging platform of the company</i></li>
            <li><b>2.Viber</b> - <i>Coordination with clients using this messaging platform</i></li>
            <li><b>3.CamScanner</b> -<i>Scanning documents using the mobile phone's camera</i></li>
            <li><b>4.Gmail</b> - <i>Emailing clients, vendors, colleagues, and for other official business purposes</i></li>
        </ol>
    </p> 
    </div>
    <hr>
    <div class="section">
        <h3>WHAT IS NOT ALLOWED</h3>
        <ol style="padding-left: 0;">
            <li>1.Playing games on company-issued and/or personal mobile phones during work hours.</li>
            <li>2.Downloading games and/or social media applications (not approved by management) on company-issued cellular phones.</li>
            <li>3.Using the mobile phone's camera or microphone to record confidential information.</li>
            <li>4.Uploading or saving obscene material on the company phone’s storage or using the company internet for such content.</li>
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
    <ol style="padding-left: 0;">
        <li><strong>1.Replacement or Repair</strong> – <i>The purchasing of replacement or repair of the asset shall be done by the company’s Supply Chain Management Department through accredited vendors and service providers.</i></li>
        <li><strong>2.Payment</strong> – <i>Expenses may be covered through salary deduction or may be paid in full via bank transfer or cash payment.</i></li>
        <li><strong>3.Time Frame for Replacing or Repairing</strong> – <i>The replacement or repair process must be completed within thirty (30) days.</i></li>
    </ol>
</div>
    <hr>

    <h3>ASSET DETAILS</h3>
    <div class="asset-details">
        <div>
            <p><strong>Asset Name:</strong> {{ $asset->assets->name }}</p>
            <p><strong>Item Name:</strong> {{ $asset->assets->item_name }}</p>
            <p><strong>Asset Serial No.:</strong> {{ $asset->assets->serial_number }}</p>
            <p><strong>Asset Category:</strong> {{ $asset->assets->category->name }}</p>
            <p><strong>Remarks:</strong> {{ $asset->description }}</p>
            <p><strong>Purchase Date:</strong> {{ $asset->purchase_date ?? '' }}</p>
            <p><strong>Gross Purchase Amount:</strong> {{ number_format($asset->assets->price, 2) }}</p>
            <br>
            <br>
            <br>
            <p><strong>Checked and Issued By :</strong> _______________</p>
            <p><strong>Approved By :</strong> _______________</p>
        </div>
        <div>
            <p><strong>Location:</strong> {{ $user->branch->name ?? '' }}</p>
            <p><strong>Rider:</strong> {{ $user->designation->designation_name ?? '' }}</p>
            <p><strong>Department:</strong> {{ $user->department->department_name ?? '' }}</p>
            <br> 
            <p>
                    I have Received the item/equipment in good working condition and by
                    affixing my signature I am acknowledging the responsibility and duty to
                    preserve and maintain the good working condition of this equipment /
                    item / tool and that any damage / loss on the company's property I shall
                    replace and pay for the said equipment within 30 days:
                </p>
                <br>
                <p><strong>Received by :</strong> _______________</p>
                <p><strong>Date Signed :</strong> _______________</p>
        </div>
    </div> 
    
    </div>
 
</body>  
</html>
