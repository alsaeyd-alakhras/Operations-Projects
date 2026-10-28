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
                    <td colspan="11" style="border:0;">
                        <p>
                            <span>مستشفى يافا الطبي</span> /
                            <span>مشروع إجراء عمليات جراحية وتدخلات طبية</span> /
                            <span>الممولين : 
                                @foreach ($financiers as $financier)
                                    {{ $financier }} ,
                                @endforeach
                            </span>
                        </p>
                    </td>
                </tr>
                <tr>
                    <td colspan="11" align="center" style="color: #000;border:0;">
                        <h1>مشروع العمليات الطبية</h1>
                    </td>
                </tr>
                <tr style="background: #dddddd;">
                    <th>#</th>
                    <th>تمت <br> العملية</th>
                    <th>التاريخ</th>
                    <th style="white-space: nowrap;">الاسم</th>
                    <!-- <th>رقم <br> الممول</th> -->
                    <!-- <th>الممول</th> -->
                    <th>السكن</th>
                    <th>العمر</th>
                    <th>الهوية</th>
                    <th>جوال 1</th>
                    <th>جوال 2</th>
                    <th style="white-space: nowrap;">العملية</th>
                    <th style="white-space: nowrap;">الطبيب</th>
                    <th>التكلفة</th>
                    <th>الملاحظات</th>
                    <th>الملاحظات الثانوية</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($records as $record)
                    <tr>
                        <td>{{$loop->iteration}}</td>
                        <td>
                            @if ($record['done'] == 1)
                                <p style="font-family: Helvetica, Arial, Sans-Serif; font-size: 16px;">&#9745;</p>
                            @endif
                        </td>
                        <td>{{$record['date']}}</td>
                        <td style="white-space: nowrap;">{{$record['name']}}</td>
                        <!-- <td style="text-align: center;">{{$record['financier_number']}}</td> -->
                        <!-- <td>{{ App\Models\Financier::where('financier_number',$record['financier_number'])->first() ? App\Models\Financier::where('financier_number',$record['financier_number'])->first()->name : '---'  }}</td> -->
                        <td>{{$record['address']}}</td>
                        <td>{{$record['age']}}</td>
                        <td>{{$record['patient_ID']}}</td>
                        <td>{{$record['phone_number1']}}</td>
                        <td>{{$record['phone_number2']}}</td>
                        <td style="white-space: nowrap;">{{$record['operation']}}</td>
                        <td style="white-space: nowrap;">{{$record['doctor']}}</td>
                        <td>{{$record['amount']}}</td>
                        <td>{{$record['notes']}}</td>
                        <td>{{$record['notes_2']}}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td>00</td>
                    <td colspan="11">المجموع</td>
                    <td>{{number_format($recordsTotalArray['amount'])}}</td>
                    <td></td>
                    <td></td>
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
