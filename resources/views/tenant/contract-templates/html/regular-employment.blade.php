<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contract for Regular Employment</title>
    <link rel="stylesheet" href="{{ asset('assets/css/contract-template.css') }}">
    <style>
        @page {
            size: A4;
            margin: 0.5in;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            line-height: 1.5;
            color: #000;
            margin: 0;
            padding: 0;
            background: #fff;
        }

        .contract-page {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            padding: 20mm;
            background: white;
            box-sizing: border-box;
            position: relative;
        }

        .contract-footer {
            position: absolute;
            bottom: 10mm;
            left: 20mm;
            right: 20mm;
            text-align: center;
            font-size: 9pt;
            background: #000;
            color: #fff;
            padding: 5px 10px;
            border-radius: 3px;
        }

        @media print {
            body {
                background: white;
            }

            .contract-page {
                margin: 0;
                padding: 20mm;
                box-shadow: none;
                page-break-after: always;
                page-break-inside: avoid;
            }

            .contract-page:last-child {
                page-break-after: auto;
            }
        }

        @media screen {
            .contract-page {
                margin-bottom: 20px;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            }
        }
    </style>
</head>

<body>
    <div class="contract-page">
        <!-- Header with Logos -->
        <div class="contract-header">
            <div class="logo-container">
                <div class="logo-left">
                    <img src="{{ asset('build/img/theos.png') }}" alt="Theos Helios Logo" class="logo"
                        style="max-width: 100px; height: auto;">
                </div>
                <div class="header-center">
                    <div class="company-name">THEOS HELIOS SECURITY AGENCY, CORP.</div>
                    <div class="company-tagline">'WE SECURE. YOU PROSPER'</div>
                    <div class="contract-title">CONTRACT FOR REGULAR EMPLOYMENT</div>
                    <div class="contract-subtitle">REG – XX series of 2024</div>
                    <div class="page-number">Page 1 of 5</div>
                </div>
                <div class="logo-right">
                    <img src="{{ asset('build/img/theos-hrd.png') }}" alt="Theos Helios Seal" class="logo"
                        style="max-width: 100px; height: auto;">
                </div>
            </div>
        </div>

        <!-- Section Title -->
        <div class="section-title">KNOW ALL MEN BY THESE PRESENTS</div>

        <!-- Contract Opening -->
        <div class="contract-body">
            This Contract of Employment is made and executed at Quezon City on <span
                class="variable-placeholder">{{ $contractData['start_date'] ?? 'DATE' }}</span> by and between:
        </div>

        <!-- Employer Section -->
        <div class="party-section">
            <span class="employer-name">THEOS HELIOS SECURITY AGENCY, CORP.</span>, a registered corporation duly
            organized and existing under Philippine laws, principally engaged in the business of providing security
            services, with office address at IBM Plaza, 8 Eastwood Avenue, Eastwood City, Cyberpark, E. Rodriguez Jr.
            (C5), Bagumbayan, Quezon City, and herein represented by its President, Alexis Alberi P. Torio, and
            hereinafter referred to as "EMPLOYER";
        </div>

        <!-- And -->
        <div class="centered-text">
            And
        </div>

        <!-- Employee Section -->
        <div class="party-section">
            <span class="variable-placeholder">{{ $contractData['party_name'] ?? 'EMPLOYEE NAME' }}</span>, of legal
            age, hereinafter referred to as the "EMPLOYEE".
        </div>

        <!-- Witnesseth -->
        <div class="centered-text">
            WITNESSETH:
        </div>

        <!-- Terms and Conditions -->
        <div class="contract-body no-indent" style="margin-top: 30px;">
            PARTIES have agreed on the following terms and conditions, as follows:
        </div>

        <!-- Position and Compensation -->
        <div class="contract-body no-indent" style="margin-top: 20px;">
            <strong>POSITION AND COMPENSATION</strong><br>
            Please see your signed JOB OFFER or INPA (Internal Personal Action)
        </div>

        <!-- Other Compensation -->
        <div class="contract-body no-indent" style="margin-top: 20px;">
            <strong>OTHER COMPENSATION / BENEFITS</strong><br>
            Mandatory Benefits : SSS, Philhealth and PagIBIG<br>
            Life Insurance<br>
            Meal Allowance (only applicable to those employees that are physically present at the office)<br>
            and any other labor benefits that the law confers on employees
        </div>

        <!-- Authorized Leaves -->
        <div class="contract-body no-indent" style="margin-top: 20px;">
            <strong>AUTHORIZED LEAVES</strong><br>
            <strong>Sick Leave (SL)</strong><br>
            <em>Shall be allocated Five (5) Days at the start of the year<br>
                SL balance at the end of the year shall be convertible to cash</em>
        </div>

        <div class="contract-body no-indent" style="margin-top: 15px;">
            <strong>Vacation Leave (VL)</strong><br>
            <em>VL of Five (5) Days shall be allocated equally for every quarter of the year<br>
                VL Balance shall be carry forwarded to incoming year and be valid for one (1) year</em>
        </div>

        <!-- Term of Employment -->
        <div class="contract-body no-indent" style="margin-top: 20px;">
            <strong>TERM OF EMPLOYMENT, RESPONSIBILITY, JOB FUNCTIONS, COMPANY POLICY AND STANDARD</strong>
        </div>

        <div class="party-section">
            In view of the very satisfactory performance during the training and probationary employment periods, the
            EMPLOYEE (<span class="variable-placeholder">{{ $contractData['party_name'] ?? 'EMPLOYEE NAME' }}</span>) is
            hereby declared <strong>PERMANENT or REGULAR</strong> effective <span
                class="variable-placeholder">{{ $contractData['start_date'] ?? 'DATE' }}</span>, and shall enjoy all the
            benefits, rights and privileges as a permanent employee.
        </div>

        <div class="party-section">
            Since the EMPLOYER had invested so much efforts to train the subject EMPLOYEE during said periods, the
            latter hereby categorically undertakes <strong>to complete at least two (2) years for staff level and at
                least three (3) years for managerial level</strong>, of employment with the Company and commits self not
            to resign, <strong>for staff level : atleast two (2) years and for managerial level : at least three (3)
                years</strong>. Failure to comply herewith shall render the EMPLOYEE liable for liquidated damages in
            favor of the EMPLOYER in the amount of IN PESOS : ONE HUNDRED THOUSAND (P100,000.00) for staff level and IN
            PESOS : TWO HUNDRED FIFTY THOUSAND (P250,000.00) for managerial level.
        </div>

        <div class="party-section">
            The EMPLOYEE agrees to perform with utmost diligence, responsibility and accountability all the duties and
            responsibilities indicated in his/her JOB FUNCTIONS attached herewith as ANNEX "A", together with such other
            duties and functions germane thereto and as may be assigned, from time to time, by the EMPLOYER. These shall
            serve as EMPLOYMENT STANDARDS where the EMPLOYEE should ANNUALLY garner at least a SATISFACTORY evaluation
            or assessment from peers, superiors and administrators, otherwise, the same shall be a ground for
            disciplinary action and process;
        </div>
        <div class="contract-footer">
            (02) 8-282-0771 | hr.thsac@gmail.com | www.guards.ph
        </div>
    </div>

    <!-- Page 2 -->
    <div class="contract-page" style="page-break-before: always;">
        <!-- Page 2 Header -->
        <div class="contract-header">
            <div class="logo-container">
                <div class="logo-left">
                    <img src="{{ asset('build/img/theos.png') }}" alt="Theos Helios Logo" class="logo"
                        style="max-width: 100px; height: auto;">
                </div>
                <div class="header-center">
                    <div class="company-name">THEOS HELIOS SECURITY AGENCY, CORP.</div>
                    <div class="company-tagline">'WE SECURE. YOU PROSPER'</div>
                    <div class="contract-title">CONTRACT FOR REGULAR EMPLOYMENT</div>
                    <div class="contract-subtitle">REG – XX series of 2024</div>
                    <div class="page-number">Page 2 of 5</div>
                </div>
                <div class="logo-right">
                    <img src="{{ asset('build/img/theos-hrd.png') }}" alt="Theos Helios Seal" class="logo"
                        style="max-width: 100px; height: auto;">
                </div>
            </div>
        </div>

        <!-- Company Rules and Regulations -->
        <div class="party-section">
            The EMPLOYEE hereby fully agrees and commit herself/himself to comply with all the company rules and
            regulations that have been set forth and may be promulgated by the EMPLOYER and to abide by the latter's
            Code of Conduct, Core Values and Employment Standards. Working harmoniously and cooperatively with peers,
            superiors, industry partners, stakeholders and company clients is highly expected and required;
        </div>

        <div class="party-section">
            The EMPLOYEE may be required to represent the company as witnesses in any claim or case filed in any court
            of justice, administrative tribunals or agencies, involving the company and any and all of its transactions.
        </div>

        <div class="party-section">
            The EMPLOYEE fully understands that in case s/he is found unqualified or unfit to perform his/her job after
            a careful evaluation of his/her performance, or found to have been committed an infraction of company
            policies, the EMPLOYER may impose disciplinary sanctions on him/her, including termination or dismissal,
            after conferment of procedural due process.
        </div>

        <div class="party-section">
            Any company-issued equipment to the employee such as, but not limited to, laptops, iPads, tablets, or mobile
            phones, is the property of the company and shall remain as such despite being in the possession of the
            employee.
        </div>

        <div class="party-section">
            It shall be understood that, whether before or after the assignment of the equipment to the employee, all
            installed software in the company-issued equipment are likewise the ownership of EMPLOYER. As such, the
            company reserves the right to inspect the company-issued equipment and the installed software therein for
            purposes of investigation against the employee to determine any bad faith, wanton, malicious, reckless or
            abusive intentions, or fraud or deceit on the part of the employee.
        </div>

        <div class="party-section">
            The EMPLOYEE that have been issued any company property, equipment and/or gadget, such as but not limited
            to, laptops, sim card, mobile phones, motorcycles, vehicles, and others shall be held accountable and liable
            therefor in case of any damage or loss thereof, after conferring procedural due process.
        </div>

        <div class="party-section">
            EMPLOYEE further acknowledges that to the extent Employee is engaged in sales and/or other official business
            dealings with clients / customers / vendors / suppliers / service providers / consultants Employee develops
            substantial good will on behalf of Employer by dealing with clients / customers / vendors / suppliers /
            service providers / consultants. Such good will is, in all instances, the property of Employer. Employee
            further acknowledges that any solicitation of customers in violation of this agreement would be a
            misappropriation of customer good will to the substantial detriment of the Employer.
        </div>

        <!-- Working Days -->
        <div class="contract-body no-indent" style="margin-top: 20px;">
            <strong>WORKING DAYS</strong>
        </div>

        <div class="party-section">
            The EMPLOYEE agrees to report for work for six (6) days weekly where the rest day or day-off shall be
            exclusively determined by the EMPLOYER depending on the exigencies of service and is not necessarily on a
            Sunday. The 6-day workweek may be reduced to only five (5) days, at the discretion of the EMPLOYER, and when
            so reduced, it shall not be deemed as company practice as it could be unilaterally reverted back to 6-day
            workweek using the same discretion;
        </div>

        <!-- Consent to Deposit -->
        <div class="contract-body no-indent" style="margin-top: 20px;">
            <strong>CONSENT TO DEPOSIT FIRST SALARY AS SECURITY FOR COMPLIANCE</strong>
        </div>

        <div class="party-section">
            As the Employee has no available cash or surety bond upon employment as required by Company policy on
            security deposit, the Employee fully agrees to deposit his/her first net salary with the Employer, and
            hereby fully and unconditionally authorizes and gives consent to the Employer to withhold the release of
            said net salary to serve as his/her compliance with said policy for the purpose of serving as security
            deposit for losses, damages, breach of contract resulting to financial damage to the Company,
            pre-termination of contract, non-observance of 30-day prior notice in case of resignation, abandonment of
            duty or work, lack of turnover of responsibilities upon separation, failure to secure and complete exit
            clearance, breach of confidentiality, any act of negligence and/or misconduct resulting to company losses,
            and all similar or analogous cases. The Employee hereby authorizes and gives consent to the Employer to
            deduct, upon due investigation and due process, from said security deposit any loss or damage sustained or
            incurred by the Employer or any of its clients, and in case the same is insufficient, from all the
            Employee's remaining receivables from the Employer, without prejudice to filing of separate court actions if
            necessary. In case the Employee is cleared upon separation from employment for whatever reason, said
            security deposit is refundable without interest by the Employer.
        </div>
        <div class="contract-footer">
            (02) 8-282-0771 | hr.thsac@gmail.com | www.guards.ph
        </div>
    </div>

    <!-- Page 3 -->
    <div class="contract-page" style="page-break-before: always;">
        <!-- Page 3 Header -->
        <div class="contract-header">
            <div class="logo-container">
                <div class="logo-left">
                    <img src="{{ asset('build/img/theos.png') }}" alt="Theos Helios Logo" class="logo"
                        style="max-width: 100px; height: auto;">
                </div>
                <div class="header-center">
                    <div class="company-name">THEOS HELIOS SECURITY AGENCY, CORP.</div>
                    <div class="company-tagline">'WE SECURE. YOU PROSPER'</div>
                    <div class="contract-title">CONTRACT FOR REGULAR EMPLOYMENT</div>
                    <div class="contract-subtitle">REG – XX series of 2024</div>
                    <div class="page-number">Page 3 of 5</div>
                </div>
                <div class="logo-right">
                    <img src="{{ asset('build/img/theos-hrd.png') }}" alt="Theos Helios Seal" class="logo"
                        style="max-width: 100px; height: auto;">
                </div>
            </div>
        </div>

        <!-- Data Privacy Consent -->
        <div class="contract-body no-indent" style="margin-top: 20px;">
            <strong>DATA PRIVACY CONSENT</strong>
        </div>

        <div class="party-section">
            The employee hereby voluntarily gives his / her written consent to the giving and/or disclosure of all
            material information about himself / herself as required for his / her employment and deployment under THEOS
            HELIOS SECURITY AGENCY, CORP. (THSAC)
        </div>

        <div class="party-section">
            The employee fully understands that the THSAC administrators, officers, and partners need all this material
            information about the employee for the protection of the institution and wherever deployment may be
            assigned. The employee hereby gives full authority to any authorized THSAC representative or personnel to
            exercise full discretion in sharing any, some or all of these disclosed information to anyone for
            employment, coordination, evaluation, assessment and/or referral purposes, as s/he deems fit or necessary
            under the circumstances, but such discretion shall be exercised with utmost care, caution and dispatch.
        </div>

        <!-- Non-Reference & Data Privacy Clause -->
        <div class="contract-body no-indent" style="margin-top: 20px;">
            <strong>NON-REFERENCE & DATA PRIVACY CLAUSE</strong>
        </div>

        <div class="party-section">
            The Employee agrees that during and after their employment with the Company, they shall not use the
            following company details, but not limited to, its owners, its employees, its clients, the Company address,
            or any Company contact details as a reference for any personal transactions. This includes, but is not
            limited to, financial transactions, credit applications, and personal references.
        </div>

        <div class="party-section">
            The Employee acknowledges that using such information constitutes a breach of the data privacy of the
            Company, its owners, its employees, and its clients. Any violation of this clause will result in
            disciplinary action, up to and including termination of employment and potential legal action.
        </div>

        <!-- Non-Disclosure Agreement -->
        <div class="contract-body no-indent" style="margin-top: 20px;">
            <strong>NON-DISCLOSURE AGREEMENT / DATA PRIVACY LAW</strong>
        </div>

        <div class="party-section">
            The EMPLOYEE fully understands and recognizes the extent and effect of the Data Privacy Law which punishes
            releasing, divulging and/or processing of sensitive and personal information of individuals, peers,
            superiors, industry partners, stakeholders and company clients which are thus considered as strictly
            confidential. In addition to these personal and sensitive information laid down under the law which the
            EMPLOYEE shall obtain and/or have access to during employment, all other matters, detail and/or information
            pertaining to the day-to-day security operations, business processes and management operations of the
            EMPLOYER are hereby considered and agreed to be personal to the Company, sensitive and strictly
            confidential. Thus, the EMPLOYEE is strictly required to maintain such information in strict confidence
            which should not be released and divulged to anyone and in any form, whether directly or indirectly,
            verbally or in writing, personally or anonymously, including future employers, business partners, business
            competitors, and government agencies;
        </div>

        <div class="party-section">
            The security operations, business processes and management operations of the EMPLOYER, including trade
            secrets, business strategies, and proprietary operations, consist of, but are not necessarily limited to:
        </div>

        <div class="party-section" style="padding-left: 30px;">
            1. Technical information: Methods, processes, formulae, compositions, systems, techniques, innovations,
            machines, computer programs and research projects; and<br><br>
            2. Business information: Customer lists, pricing, data, sources of supply, financial data, marketing
            production, or merchandising systems or plans.
        </div>

        <div class="party-section">
            In case of complaints or grievances, EMPLOYEE shall not air the same to the media whether in print or
            broadcast media, the internet or social media. In event the EMPLOYEE violates this condition, The EMPLOYER
            shall be entitled to damages as reparation for the injurious act.
        </div>

        <!-- Termination, Resignation -->
        <div class="contract-body no-indent" style="margin-top: 20px;">
            <strong>TERMINATION, RESIGNATION, END OF CONTRACT</strong>
        </div>

        <div class="party-section">
            Upon termination or resignation from employment, the EMPLOYEE shall return to the EMPLOYER, with proper
            documentation and endorsement, all pending jobs, projects, pending work/issues/concern by the company's
            client and other necessary works assigned to the employee and also all its documents and property, including
            but not necessarily limited to: reports, manuals, correspondence, customer lists, computer programs and
            files, and all other materials and all copies thereof relating in any way to its operations, or in any way
            obtained during the course of employment; also the EMPLOYER shall process the termination or resignation for
            not less than thirty (30) working days, the days of turnover may be adjusted what deemed necessary by their
            immediate supervisor and / or top management but this may not be more than Sixty (60) working days, during
            the turnover period the Employee's last salary cut-off, pro-rated 13th month, and other receivables and
            incentives, shall be released after the employee has
        </div>
        <div class="contract-footer">
            (02) 8-282-0771 | hr.thsac@gmail.com | www.guards.ph
        </div>
    </div>

    <!-- Page 4 -->
    <div class="contract-page" style="page-break-before: always;">
        <!-- Page 4 Header -->
        <div class="contract-header">
            <div class="logo-container">
                <div class="logo-left">
                    <img src="{{ asset('build/img/theos.png') }}" alt="Theos Helios Logo" class="logo"
                        style="max-width: 100px; height: auto;">
                </div>
                <div class="header-center">
                    <div class="company-name">THEOS HELIOS SECURITY AGENCY, CORP.</div>
                    <div class="company-tagline">'WE SECURE. YOU PROSPER'</div>
                    <div class="contract-title">CONTRACT FOR REGULAR EMPLOYMENT</div>
                    <div class="contract-subtitle">REG – XX series of 2024</div>
                    <div class="page-number">Page 4 of 5</div>
                </div>
                <div class="logo-right">
                    <img src="{{ asset('build/img/theos-hrd.png') }}" alt="Theos Helios Seal" class="logo"
                        style="max-width: 100px; height: auto;">
                </div>
            </div>
        </div>

        <div class="party-section">
            fulfilled its obligations to the company, any losses and/or penalties incurred by the company due to the
            negligence of the EMPLOYEE shall be deducted to the last pay of EMPLOYEE;
        </div>

        <!-- Non-Compete and Non-Disclosure Agreement -->
        <div class="contract-body no-indent" style="margin-top: 20px;">
            <strong>NON COMPETE AND NON-DISCLOSURE AGREEMENT / DATA PRIVACY LAW</strong>
        </div>

        <div class="party-section">
            The EMPLOYEE fully understands and recognizes the extent and effect of the Data Privacy Law which punishes
            releasing, divulging and/or processing of sensitive and personal information of individuals, peers,
            superiors, industry partners, stakeholders and company clients which are thus considered as strictly
            confidential. In addition to these personal and sensitive information laid down under the law which the
            EMPLOYEE shall obtain and/or have access to during employment, all other matters, detail and/or information
            pertaining to the day-to-day security operations, business processes and management operations of the
            EMPLOYER are hereby considered and agreed to be personal to the Company, sensitive and strictly
            confidential. Thus, the EMPLOYEE is strictly required to maintain such information in strict confidence
            which should not be released and divulged to anyone and in any form, whether directly or indirectly,
            verbally or in writing, personally or anonymously, including future employers, business partners, business
            competitors, and government agencies;
        </div>

        <div class="party-section">
            The security operations, business processes and management operations of the EMPLOYER, including trade
            secrets, business strategies, and proprietary operations, consist of, but are not necessarily limited to:
        </div>

        <div class="party-section" style="padding-left: 30px;">
            1. Technical information: Methods, processes, formulae, compositions, systems, techniques, innovations,
            machines, computer programs and research projects; and<br><br>
            2. Business information: Customer lists, pricing, data, sources of supply, financial data, marketing
            production, or merchandising systems or plans.
        </div>

        <!-- Restrictive Covenant -->
        <div class="contract-body no-indent" style="margin-top: 20px;">
            <strong>RESTRICTIVE COVENANT</strong>
        </div>

        <div class="party-section">
            At all times while this agreement is in force and after its expiration or termination, the Employee agrees
            for ten (10) years to refrain from disclosing the Employer's customer lists, trade secrets, or other
            confidential material also the employee agrees to take reasonable security measures to prevent accidental
            disclosure and industrial espionage. While this agreement is in force, to abide by the nondisclosure and
            noncompetition terms of this agreement. After expiration or termination of this agreement, the Employee
            agrees not to compete with the Employer for a period of Three (3) years anywhere in the National Capital
            Region (NCR) of the Philippines. Competition would mean that the Employee will not engage in owning or
            working for a business in the same industry as the Employer or its affiliate.
        </div>

        <div class="party-section">
            For so long as Employee is employed by Employer and for a period of ten (10) years after the termination of
            Employee's employment for any reason whatsoever, whether voluntarily or involuntarily, Employee shall not
            directly or indirectly, individually or for any person, firm or employee solicit, divert, interfere with,
            disturb or take away, or attempt to solicit, divert, interfere with, disturb or take away the patronage of:
        </div>

        <div class="party-section" style="padding-left: 30px;">
            1. any client or prospective client of the Employer at any time within ten (10) years prior to termination
            of Employee's employment,<br><br>
            2. any entity that was a client of the Employer at any time within ten (10) years prior to the termination
            of Employee's employment, or<br><br>
            3. any client that acquired services from the Employer during any time within ten (10) years prior to
            termination of Employee's employment (collectively, the clients and prospective clients listed in (i), (ii)
            and (iii) shall be referred to as a "THSAC Client")
        </div>

        <div class="party-section">
            Employee further acknowledges that to the extent Employee is engaged in sales and/or dealings with
            customers, Employee develops substantial good will on behalf of Employer by dealing with customers. Such
            customer good will is, in all instances, the property of Employer. Employee further acknowledges that any
            solicitation of customers in violation of this agreement would be a misappropriation of customer good will
            to the substantial detriment of the Employer.
        </div>

        <div class="party-section">
            Employee agrees to pay liquidated damages in the amount below for the respective positions, for any
            violation under the Paragraph 2 Restrictive Covenant
        </div>

        <div class="party-section" style="padding-left: 30px;">
            <strong>MANAGERIAL LEVEL PhP 1,000,000.00</strong><br>
            (Asst. Managers / Managers / Department Heads / Supervisors / Consultant / Other position that is related to
            the managerial position)
        </div>
        <div class="contract-footer">
            (02) 8-282-0771 | hr.thsac@gmail.com | www.guards.ph
        </div>
    </div>

    <!-- Page 5 -->
    <div class="contract-page" style="page-break-before: always;">
        <!-- Page 5 Header -->
        <div class="contract-header">
            <div class="logo-container">
                <div class="logo-left">
                    <img src="{{ asset('build/img/theos.png') }}" alt="Theos Helios Logo" class="logo"
                        style="max-width: 100px; height: auto;">
                </div>
                <div class="header-center">
                    <div class="company-name">THEOS HELIOS SECURITY AGENCY, CORP.</div>
                    <div class="company-tagline">'WE SECURE. YOU PROSPER'</div>
                    <div class="contract-title">CONTRACT FOR REGULAR EMPLOYMENT</div>
                    <div class="contract-subtitle">REG – XX series of 2024</div>
                    <div class="page-number">Page 5 of 5</div>
                </div>
                <div class="logo-right">
                    <img src="{{ asset('build/img/theos-hrd.png') }}" alt="Theos Helios Seal" class="logo"
                        style="max-width: 100px; height: auto;">
                </div>
            </div>
        </div>

        <div class="party-section" style="padding-left: 30px;">
            <strong>STAFF LEVEL PhP 250,000.00</strong><br>
            (Division Asst. / Department Asst. / Department Staff / Admin Aide / Collector / Messenger / Other position
            that is not holding a managerial level position)
        </div>

        <!-- Employment at Will -->
        <div class="contract-body no-indent" style="margin-top: 20px;">
            <strong>EMPLOYMENT AT WILL</strong>
        </div>

        <div class="party-section">
            Nothing in this agreement shall be construed as a promise or agreement of any kind, express or implied, of
            employment for specific duration.
        </div>

        <!-- Acknowledgement -->
        <div class="contract-body no-indent" style="margin-top: 20px;">
            <strong>ACKNOWLEDGEMENT OF VOLUNTARINESS AND CONSIDERATION</strong>
        </div>

        <div class="party-section">
            Employee acknowledges that she/he understands the provisions of this agreement, that the agreement is
            entered into knowingly and voluntarily, and that Employee has been afforded a sufficient amount of time to
            consider the agreement and to consult with and seek the advice of any person of Employee's choosing,
            including an attorney. Employee further acknowledges Employee has received adequate and sufficient
            consideration to support the agreement.
        </div>

        <!-- Material Violation -->
        <div class="contract-body no-indent" style="margin-top: 20px;">
            <strong>MATERIAL VIOLATION and LIABILITY</strong>
        </div>

        <div class="party-section">
            Any material violation committed by the EMPLOYEE of this Employment Contract shall result to the (a)
            forfeiture of all receivables from the EMPLOYER, whether actually earned or accrued and whether in the form
            of salaries or labor benefits; (b) pre-termination of this Contract; and/or (c) payment of liquidated
            damages in an amount not less than PhP 100,000.00 on top of the stated liquidated damages on this agreement;
        </div>

        <div class="party-section">
            All terms and conditions of this contract shall be construed under the context of the laws of the
            Philippines. In case of litigation arising from or in connection with this contract, venue of action shall
            be in the proper Regional or Metropolitan Trial Court of Quezon City and the amount equivalent to
            twenty-five percent (25%) of the amount claimed shall be due and demandable as attorney's fees.
        </div>

        <!-- Witness Whereof -->
        <div class="party-section" style="margin-top: 40px;">
            IN WITNESS WHEREOF, the parties hereto have affixed their signatures at Quezon City on this _____ day of
            ________________________, 20____.
        </div>

        <!-- Signature Section -->
        <div style="margin-top: 60px;">
            <div style="margin-bottom: 50px;">
                <div style="border-bottom: 2px solid #000; width: 500px; display: inline-block;"></div>
                <div style="margin-top: 5px;">
                    <strong><span
                            class="variable-placeholder">{{ $contractData['party_name'] ?? 'EMPLOYEE NAME' }}</span>,
                        Employee</strong>
                </div>
            </div>

            <div style="margin-bottom: 50px;">
                <strong>THEOS HELIOS SECURITY AGENCY CORP</strong>.<br>
                Employer
            </div>

            <div style="margin-top: 40px;">
                <div><strong>Witnesses:</strong></div>
                <div style="display: flex; gap: 40px; margin-top: 60px;">
                    <div style="flex: 1;">
                        <div style="border-bottom: 2px solid #000;"></div>
                    </div>
                    <div style="flex: 1;">
                        <div style="border-bottom: 2px solid #000;"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="contract-footer">
            (02) 8-282-0771 | hr.thsac@gmail.com | www.guards.ph
        </div>
    </div>
</body>

</html>