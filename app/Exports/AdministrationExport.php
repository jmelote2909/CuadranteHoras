<?php

namespace App\Exports;

use App\Models\Operator;
use App\Traits\CalculatesMonthlyTotals;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class AdministrationExport implements FromView, WithStyles, WithColumnWidths, WithTitle
{
    use CalculatesMonthlyTotals;

    protected $month;
    protected $year;
    protected $company;

    public function __construct($month, $year, $company)
    {
        $this->month = $month;
        $this->year = $year;
        $this->company = $company;
    }

    public function view(): View
    {
        $allOperators = Operator::where('company', $this->company)->get();
        $totals = $this->calculateTotals($allOperators, $this->month, $this->year, false);
        
        // Filter operators to only show those with cost > 0
        $operators = $allOperators->filter(function($op) use ($totals) {
            return ($totals[$op->id]['coste_total'] ?? 0) > 0;
        });

        $currentMonthName = Carbon::createFromDate($this->year, $this->month, 1)->translatedFormat('F');
        $startDate = Carbon::createFromDate($this->year, $this->month, 1)->format('d/m/Y');
        $endDate = Carbon::createFromDate($this->year, $this->month, 1)->endOfMonth()->format('d/m/Y');

        return view('exports.administration', [
            'operators' => $operators,
            'totals' => $totals,
            'company' => $this->company,
            'monthName' => $currentMonthName,
            'year' => $this->year,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        // Header Info (Company, Month, Year, Period) - Rows 3 to 5
        $sheet->getStyle('A3:G5')->getFont()->setBold(true);
        $sheet->getStyle('A3:G5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        // Main Table Headers (Row 7)
        $headersRange = 'A7:G7';
        $sheet->getStyle($headersRange)->getFont()->setBold(true);
        $sheet->getStyle($headersRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($headersRange)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($headersRange)->getAlignment()->setWrapText(true);
        $sheet->getRowDimension(7)->setRowHeight(40);
        
        // Colors from screenshot
        $sheet->getStyle('A7')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('C6E0B4'); // Operario
        $sheet->getStyle('B7')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('E2EFDA'); // L-V
        $sheet->getStyle('C7')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('F4B084'); // Saturday
        $sheet->getStyle('D7')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF7C80'); // Sunday
        $sheet->getStyle('E7')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('B1A0C7'); // Holiday
        $sheet->getStyle('F7')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('70AD47'); // Cost
        $sheet->getStyle('F7')->getFont()->getColor()->setARGB('FFFFFF'); // Cost (White text)
        $sheet->getStyle('G7')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('BDD7EE'); // Zone

        // Borders for the whole table
        $lastRow = $sheet->getHighestRow();
        $sheet->getStyle('A7:G' . $lastRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        
        // Specific styling for the Operario column (Column A)
        $sheet->getStyle('A7:A' . $lastRow)->getFont()->setBold(true);
        $sheet->getStyle('A7:A' . $lastRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('C6E0B4');
        $sheet->getStyle('A7:A' . $lastRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        // Data alignment
        $sheet->getStyle('B8:G' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('B8:G' . $lastRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 45, // Operario
            'B' => 20, // L-V
            'C' => 15, // Saturday
            'D' => 15, // Sunday
            'E' => 15, // Holiday
            'F' => 20, // Cost
            'G' => 25, // Zone
        ];
    }

    public function title(): string
    {
        return 'Administración ' . strtoupper($this->company);
    }
}
