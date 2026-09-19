<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        @page {
            margin: 20px 40px;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            direction: rtl;
            text-align: right;
            font-size: 15px;
            line-height: 1.8;
            color: #000;
        }
        .header-logo {
            text-align: center;
            width: 100%;
            margin-bottom: 25px;
        }
        .header-logo img {
            width: 85%;
            height: auto;
        }
        .recipient {
            text-align: center;
            font-weight: bold;
            font-size: 17px;
            margin: 20px 0 30px 0;
            line-height: 1.5;
        }
        .subject-box {
            margin: 20px 0;
            font-size: 15px;
        }
        .subject-box p {
            margin: 4px 0;
        }
        .content-body {
            margin-top: 25px;
            text-align: justify;
        }
        .footer {
            position: absolute;
            bottom: 15px;
            left: 0;
            right: 0;
            text-align: center;
            border-top: 1.5px solid #000;
            padding-top: 6px;
            font-size: 13px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header-logo">
        <img src="data:image/png;base64,{{ $headerImage }}" alt="Logo AREF Oriental">
    </div>

    <div class="recipient">
        مديرة الأكاديمية<br>
        إلى السيد المدير الإقليمي<br>
        المديرية الإقليمية - {{ $assignment->occupant->establishment->name_ar }}
    </div>

    <div class="subject-box">
        <p><strong><u>الموضوع:</u></strong> الموافقة على إسناد سكن وظيفي.</p>
        <p>
            <strong><u>المرجع:</u></strong>
            طلب إسناد السكن الوظيفي بتاريخ {{ $assignment->submitted_at?->format('d/m/Y') }}<br>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;المذكرة الوزارية رقم 40 بتاريخ 10 ماي 2004
        </p>
    </div>

    <p style="text-align: center; font-weight: bold; margin-top: 20px;">سلام تام بوجود مولانا الإمام</p>

    <div class="content-body">
        <p>
            وبعد، فجوابا على طلبكم المشار إليه في المرجع أعلاه، والمتضمن لطلب السيد
            <strong>{{ $assignment->occupant->full_name_ar ?: $assignment->occupant->full_name_fr }}</strong>
            رقم التأجير <strong>{{ $assignment->occupant->employee_number }}</strong>
            في شأن الموافقة على إسناد السكن الوظيفي المخصص للإدارة التربوية بـ
            <strong>{{ $assignment->logement->location_ar ?: $assignment->logement->location_fr }}</strong>
            التابعة للمديرية الإقليمية {{ $assignment->occupant->establishment->name_ar }}،
            وتبعا للمذكرة الوزارية المذكورة أعلاه، يشرفني إخباركم أن الأكاديمية توافق على إسناد هذا السكن للمكلف بالأمر بصفته
            <strong>{{ $assignment->occupant->position }}</strong>.
        </p>
    </div>

    <p style="text-align: center; font-weight: bold; margin-top: 50px;">وتقبلوا أزكى التحيات والسلام.</p>

    <div class="footer">
        قسم الشؤون الإدارية والمالية<br>
        الهاتف: 05-36-50-32-00 &nbsp;&nbsp;-&nbsp;&nbsp; الفاكس: 05-36-68-55-17
    </div>
</body>
</html>
