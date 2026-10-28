<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="utf-8" />
    <title>جدول مشروع العمليات</title>
    <style>
        body {
            font-family: 'XBRiyaz', sans-serif;
        }

        @page {
            header: page-header;
            footer: page-footer;
        }
    </style>
    <style>
        table.blueTable {
            width: 100%;
            text-align: right;
            border-collapse: collapse;
        }

        table.blueTable td,
        table.blueTable th {
            border: 1px solid #AAAAAA;
            padding: 5px 9px;
            white-space: nowrap;
        }

        table.blueTable tbody td {
            font-size: 13px;
            color: #000000;
        }

        table.blueTable tbody tr:nth-child(even) {
            background: #F5F5F5;
        }

        table.blueTable thead {
            background: #b8b8b8;
            background: -moz-linear-gradient(top, #dedede 0%, #d7d7d7 66%, #D3D3D3 100%);
            background: -webkit-linear-gradient(top, #dedede 0%, #d7d7d7 66%, #D3D3D3 100%);
            background: linear-gradient(to bottom, #dedede 0%, #d7d7d7 66%, #D3D3D3 100%);
            border-bottom: 2px solid #444444;
        }

        table.blueTable thead th {
            font-size: 18px;
            font-weight: bold;
            text-align: right;
        }

        table.blueTable tfoot {
            font-size: 14px;
            font-weight: bold;
            color: #FFFFFF;
            background: #EEEEEE;
            background: -moz-linear-gradient(top, #f2f2f2 0%, #efefef 66%, #EEEEEE 100%);
            background: -webkit-linear-gradient(top, #f2f2f2 0%, #efefef 66%, #EEEEEE 100%);
            background: linear-gradient(to bottom, #f2f2f2 0%, #efefef 66%, #EEEEEE 100%);
            border-top: 2px solid #444444;
        }

        table.blueTable tfoot td {
            font-size: 14px;
        }

        table.blueTable tfoot .links {
            text-align: right;
        }

        table.blueTable tfoot .links a {
            display: inline-block;
            background: #1C6EA4;
            color: #FFFFFF;
            padding: 2px 8px;
            border-radius: 5px;
        }
    </style>
</head>

<body>
    <htmlpageheader name="page-header">
    </htmlpageheader>

    <div lang="ar">
        <table class="blueTable">
            <thead>
                <tr>
                    <td colspan="13" style="border:0;">
                        <p>
                            <span>مستشفى يافا الطبي</span> /
                            <span>مشروع إجراء عمليات جراحية وتدخلات طبية</span>
                        </p>
                    </td>
                </tr>
                <tr>
                    <td colspan="13" align="center" style="color: #000;border:0;">
                        <h1>مشروع العمليات الطبية - حسابي</h1>
                    </td>
                </tr>
                <tr style="background: #dddddd;">
                    <th>#</th>
                    <th>التاريخ</th>
                    <th style="white-space: nowrap;">الاسم</th>
                    <th>رقم الممول</th>
                    <th>العمر</th>
                    <th style="white-space: nowrap;">العملية</th>
                    <th style="white-space: nowrap;">الطبيب</th>
                    <th style="white-space: nowrap;">طبيب التخدير</th>
                    <th>م المطلوب</th>
                    <th>حصة الطبيب</th>
                    <th>التخدير</th>
                    <th>المبيت</th>
                    <th>خاص</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($records as $record)
                <tr>
                    <td>{{$loop->iteration}}</td>
                    <td>{{ $record['date'] }}</td>  <!-- التاريخ -->
                    <td style="white-space: nowrap;">{{ $record['name'] }}</td>  <!-- الاسم -->
                    <td>{{ $record['financier_number'] }}</td>  <!-- رقم التمويل -->
                    <td>{{ $record['age'] }}</td>  <!-- العمر -->
                    <td style="white-space: nowrap;">{{ $record['operation'] }}</td>  <!-- العملية -->
                    <td style="white-space: nowrap;">{{ $record['doctor'] }}</td>  <!-- الطبيب -->
                    <td style="white-space: nowrap;">{{ $record['anesthesia'] }}</td>  <!-- التخدير -->
                    <td>{{ $record['amount'] }}</td>  <!-- المبلغ -->
                    <td>{{ $record['doctor_share'] }}</td>  <!-- حصة الطبيب -->
                    <td>{{ $record['anesthesiologists_share'] }}</td>  <!-- حصة الأطباء المساعدين -->
                    <td>{{ $record['bed'] }}</td>  <!-- السرير -->
                    <td>{{ $record['private'] }}</td>  <!-- خاص -->
                </tr>
            @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td>00</td>
                    <td colspan="7">المجموع</td>  <!-- التاريخ -->
                    <td>{{ number_format($recordsTotalArray['amount']) }}</td>  <!-- المبلغ -->
                    <td>{{ number_format($recordsTotalArray['doctor_share']) }}</td>  <!-- حصة الطبيب -->
                    <td>{{ number_format($recordsTotalArray['anesthesiologists_share']) }}</td>  <!-- حصة الأطباء المساعدين -->
                    <td>{{ number_format($recordsTotalArray['bed']) }}</td>  <!-- السرير -->
                    <td>{{ number_format($recordsTotalArray['private']) }}</td>  <!-- خاص -->
                </tr>
            </tfoot>
        </table>
        <htmlpagefooter name="page-footer">
            <table width="100%" style="vertical-align: bottom; color: #000000;  margin: 1em">
                <tr>
                    <td width="33%">{DATE j-m-Y}</td>
                    <td width="33%" align="center">{PAGENO}/{nbpg}</td>
                    @auth
                        <td width="33%" style="text-align: left;">{{ Auth::user()->name }}</td>
                    @else
                        <td width="33%" style="text-align: left;">اسم المستخدم</td>
                    @endauth
                </tr>
            </table>
        </htmlpagefooter>
    </div>


</body>

</html>
