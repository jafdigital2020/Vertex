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
            margin: 12px 0 5px; 
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
        <strong style="font-size:16px;">POLICY AND AUTHORIZATION OF COMPANY ISSUED EQUIPMENT REV.01 </strong>
        <br>
         <span>ASS-{{ explode('-', $asset->assets->name)[1] }}-{{ explode('-', $asset->assets->name)[2] }}</span> 
        </div>

    </div> 
    <div class="section">
        <h3>PURPOSE</h3>
        <p>
            This policy outlines the guidelines for the use and care of company-issued laptops. The policy is intended to ensure the security and proper use of
            company-owned laptops and to protect the company's information and assets.
        </p>
    </div>
    <hr> 
    <div class="section">
        <h3>SCOPE</h3>
        <p>
            This policy applies to all employees who are issued a company-owned laptop.
        </p>
    </div>
    <hr>
    <div class="section">
    <h3>POLICY</h3>
      <p>
        <strong>Standard Care and Safekeeping of Asset </strong>
        <ol >
            <li> Company-issued laptops are the property of the company and must be used for business purposes only.</li>
            <li>  Laptops must be kept in a secure location when not in use, and all confidential or sensitive company information must be protected with password
            protected screensavers and/or encryption. </li>
            <li> Laptops must be protected from physical damage and must be kept clean and in good working condition. Employees are responsible for reporting
            any damage or malfunction immediately to the IT department. </li>
            <li>Laptops must be returned to the company in good working condition upon termination of employment or upon request by the company</li>
        </ol>
    </p> 
     <p>
        <strong>Company's right to access, monitor and audit company asset  </strong>
        <ol >
            <li>  The company reserves the right to monitor, audit, and access the contents of company-issued laptops at any time for the purpose of ensuring
            compliance with company policies and protecting company assets. </li> 
        </ol>
    </p> 
     <p>
        <strong>Restrictions / Prohibitions</strong>
        <ol>
            <li>Employees are prohibited from installing any software or programs on company-issued laptops without the prior approval of the Supply Chain
            Management Department. </li>
            <li>Custodian or employees does not have the right to change permissions, passwords, user / admin account passowrds on the company owned laptop </li>
            <li>Employees are not allowed to install games, malware or other illegal app that may be detrimental to the operations of the company and its image.  </li>
            <li> Uploading / Saving of obscene material on the company asset and storing and using the company internet.</li>
        </ol>
    </p> 
     <p>
        <strong>Other Prohibitions and Policies </strong>
        <ol>
            <li>. Employees must comply with all applicable laws and regulations regarding the use of company-issued laptops and the handling of company
            information.  </li>
            <li> Any violation of this policy may result in disciplinary action, up to and including termination of employment.  </li> 
        </ol>
    </p> 
    <p><strong><i>REPLACEMENT OR REPAIR</i></strong> 
        <ol>
            <li><strong>Replacement or Repair</strong> – <i> The purchasing of replacement or repairing of the asset shall be done by the company's Supply Chain Management
            Department, this is to ensure it is from the accredited vendor and service provider. </i></li>
            <li><strong>Payment</strong> – <i> May be done thru SALARY DEDUCTION or may be paid in full thru bank transfer or cash payment only.</i></li>
            <li><strong>Time Frame for Replacing or Repairing</strong> – <i>replacement or repair process should be done and finalized within Thirty (30) days.</i></li>
        </ol>
    </p>
    </div> 
    <hr>
     <div class="section">
    <h3>ASSET DETAILS</h3>
    <table style="width:100%; border-collapse:collapse; font-size:12px;">
        <tr> 
            <td style="width:50%; vertical-align:top; padding:5px;">
                <p><strong>Asset Name:</strong> {{ $asset->assets->name ?? '' }}</p>
                <p><strong>Item Name:</strong> {{ $asset->assets->item_name ?? '' }}</p>
                <p><strong>Asset Serial No.:</strong> {{ $asset->assets->serial_number ?? '' }}</p>
                <p><strong>Asset Category:</strong> {{ $asset->assets->category->name ?? '' }}</p>
                <p><strong>Remarks:</strong> {{ $asset->assets->description ?? '' }}</p>
                <p><strong>Purchase Date:</strong> {{ $asset->assets->purchase_date ?? '' }}</p>
                <p><strong>Gross Purchase Amount:</strong> {{ number_format($asset->assets->price ?? 0, 2) }}</p>
            </td> 
            <td style="width:50%; vertical-align:top; padding:5px;">
                <p><strong>Location:</strong> {{ $user->branch->name ?? '' }}</p>
                <p><strong>Rider:</strong> {{ $user->designation->designation_name ?? '' }}</p>
                <p><strong>Department:</strong> {{ $user->department->department_name ?? '' }}</p>
            </td>
        </tr>
    </table> 
    </div>
    <hr>  
    <div class="section">
        <h3>ACKNOWLEDGEMENT</h3>
     <table style="width:100%; border-collapse:collapse; font-size:12px; margin-top:20px;">
    <tr>
        <td style="width:50%; vertical-align:top; padding:10px; border:0;">
            <p style="margin:6px 0;"><strong>Checked and Issued By :</strong> _______________</p>
            <p style="margin:6px 0;"><strong>Approved By :</strong> _______________</p>
        </td>
        <td style="width:50%; vertical-align:top; padding:10px; border:0;">
            <p style="margin:6px 0; text-align:justify;">
                I have received the item/equipment in good working condition and by affixing my signature 
                I acknowledge the responsibility and duty to preserve and maintain the good working 
                condition of this equipment/item/tool. Any damage or loss of the company's property 
                shall be replaced and paid for by me.
            </p>
            <p style="margin:6px 0;"><strong>Received by :</strong> _______________</p>
        </td>
        </tr>
    </table> 
    </div>
 
</body>  
</html>
