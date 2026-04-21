<table>
    <thead>
        <tr><th colspan="7"></th></tr><!-- Row 1 -->
        <tr><th colspan="7"></th></tr><!-- Row 2 -->
        <tr>
            <th colspan="2" style="font-weight: bold;">EMPRESA:</th>
            <th colspan="5" style="color: #0070C0; text-decoration: underline;">{{ strtoupper($company) }}</th>
        </tr>
        <tr>
            <th colspan="2">MES:</th>
            <th style="color: #0070C0;">{{ strtoupper($monthName) }}</th>
            <th colspan="2">AÑO:</th>
            <th colspan="2" style="color: #0070C0;">{{ $year }}</th>
        </tr>
        <tr>
            <th colspan="2">PERIODO:</th>
            <th>Del</th>
            <th style="color: #0070C0;">{{ $startDate }}</th>
            <th>al</th>
            <th colspan="2" style="color: #0070C0;">{{ $endDate }}</th>
        </tr>
        <tr><th></th></tr><!-- Spacer -->
        <tr>
            <th>OPERARIO</th>
            <th style="text-align: center;">TOTAL HORAS MES (L-V)</th>
            <th style="text-align: center;">TOTAL HORAS SÁBADO</th>
            <th style="text-align: center;">TOTAL HORAS DOMINGO</th>
            <th style="text-align: center;">TOTAL HORAS FESTIVOS</th>
            <th style="text-align: center;">COSTE MENSUAL</th>
            <th style="text-align: center;">ZONA</th>
        </tr>
    </thead>
    <tbody>
        @foreach($operators as $operator)
            <tr>
                <td>{{ $operator->name }}</td>
                <td style="text-align: center;">{{ $totals[$operator->id]['horas_lv'] }}</td>
                <td style="text-align: center;">{{ $totals[$operator->id]['horas_sab'] }}</td>
                <td style="text-align: center;">{{ $totals[$operator->id]['horas_dom'] }}</td>
                <td style="text-align: center;">{{ $totals[$operator->id]['horas_fest'] }}</td>
                <td style="text-align: center; font-weight: bold;">{{ number_format($totals[$operator->id]['coste_total'], 2, ',', '.') }} €</td>
                <td style="text-align: center;">{{ $operator->zone }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
