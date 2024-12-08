<?php
    //$seo = \App\Models\Frontend::where('data_keys', 'seo.data')->first();
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Factura Andres Te Lo Cambia</title>
        <style>
            @page { margin: 20px; }

            body { 
                margin: 20px;
                font-family: "Open Sans", "Helvetica Neue", Helvetica, Arial, sans-serif;
            }

            .table-full {
                width: 100%;
            }
            .background-red {
                color: black;
                background-color: #f1f1f9;
            }
            .font-red-bold {
                color: #FF5A00;
                font-size: 20px;
                font-weight: bold;
            }
            .font-red {
                color: #FF5A00;
            }
            .font-white {
                color: black;
                font-size: 11px;
                font-weight: bold;
            }

            .header-address-drivercar {
                color: gray;
                font-size: 13px;
            }

            .header-address-drivercar-bold {
                color: #6E6E6E;
                font-size: 14px;
                font-weight: bold;
            }

            .txt-description {
                color: gray;
                font-size: 11px;
            }
        </style>
    </head>
    <body>
        <table class="table-full" style="width: 100%"  cellspacing="0">
            <tr>
                <td style="width: 80%">
                    <h1>ANDRES TE LO CAMBIA</h1>
                </td> 
                <td style="vertical-align: top;"> 
                    <h1>FACTURA</h1>
                </td>
            </tr>
            <tr>
                <td style="width: 80%">
                    B88556501<br>
                    CALLE CARNICER 20 PISO 1-3 MADRID 28039<br>
                    INFO@DJANDRESROMAY.ES
                </td> 
                <td style="vertical-align: top;"> 
                    <img alt="{{ __($general->site_name) }}" class="img-fluid logo__is" src="{{ getImage(getFilepath('logoIcon') . '/logo.png') }}" width="100" />
                </td>
            </tr>
            <tr><td colspan="2" style="height: 80px;"></td></tr>
            @php
                $user                  = $sendMoney->user;
                // $itm = $user->address;
            @endphp
            <tr>
                <td colspan="2">
                    <table class="table-full">
                        <tr>
                            <td style="margin-top: 20px; width: 60%">
                                <b>Facturar a</b>
                            </td>
                            <td style="width: 20%; text-align:right;">
                                <b>N° de factura:</b>
                            </td>
                            <td style="width: 20%; text-align:right;">
                                {{ $sendMoney->id }}
                            </td>
                        </tr>
                        <tr>
                            <td style="height: 50px; vertical-align: top; width: 60%">
                                <p class="header-address-drivercar">
                                    {{$user->firstname}} {{$user->lastname}}<br>
                                    {{$user->mtcn_number}}<br>
                                    {{$user->email}}<br>
                                </p>
                            </td>
                            <td style="width: 20%; text-align:right;">
                                <b>Fecha:</b>
                            </td>
                            <td style="width: 20%; text-align:right;">
                                {{\Carbon\Carbon::parse($sendMoney->created_at)->format('d.m.Y')}}
                            </td>
                        </tr>
                    </table>
                </td> 
            </tr>
            <tr><td colspan="2" style="height: 30px;"></td></tr>
            <tr>
                <td style="vertical-align: top;" colspan="2"> 
                    <table class="table-full" cellspacing="0">
                        <tr style="height: 30px; background-color: #e9ecef">
                            <td style="border: solid 1px black; text-align: center; font-size: 20px; height: 50px; border-bottom: none; ">
                                <b>DESCRIPCIÓN</b>
                            </td>
                            <td style="border: solid 1px black; text-align: center; font-size: 20px; height: 50px; border-bottom: none; border-left: none;">
                                <b>IMPORTE</b>
                            </td> 
                        </tr>
                        <tr>
                            <td style="border: solid 1px black; text-align: left; font-size: 20px; height: 50px;">Servicio de intercambio de divisa Tasa {{showAmount($sendMoney->conversion_rate)}} {{$sendMoney->recipient_currency}}</td> 
                            <td style="border: solid 1px black; text-align: right; font-size: 20px; height: 50px; border-bottom: none;  border-left: none; ">{{showAmount($sendMoney->sending_amount)}} {{$sendMoney->sending_currency}}</td> 
                        </tr>
                        <tr>
                            <td style="text-align: right; font-size: 20px; border-left: none; border-bottom: none; height: 50px;"><b>TOTAL</b>&nbsp;&nbsp;&nbsp;&nbsp;</td> 
                            <td style="border: solid 1px black; text-align: right; font-size: 20px; background-color: #e9ecef; height: 50px;">{{showAmount($sendMoney->sending_amount)}} {{$sendMoney->sending_currency}}</td> 
                        </tr>
                    </table>
                </td>
            </tr>
            <tr><td colspan="2" style="height: 200px;"></td></tr>
        </table>
        
        <p>
        Condiciones y forma de pago<br/>
        Pagado
        <br/>
        </p>
    </body>
</html> 
