<?php

namespace App\Imports;

use App\Models\Record;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;


class RecordsImport implements ToModel,WithHeadingRow
{
    public function formatDate($date){
        if(is_numeric($date)){
            return Carbon::createFromFormat('Y-m-d', Date::excelToDateTimeObject($date)->format('Y-m-d'));
        }else{
            return $date;
        }
    }
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Record([
            'date' => $this->formatDate($row['altarykh']),
            'name' => $row['alasm'],
            'financier_number' => $row['mmol'],
            'age' => $row['alaamr'],
            'patient_ID' => $row['hoy'],
            'phone_number1' => $row['goal1'],
            'phone_number2' => $row['goal2'],
            'operation' => $row['alaamly'],
            'doctor' => $row['altbyb'],
            'amount' => $row['altklf'],
            'doctor_share' => $row['hs_altbyb'],
            'anesthesiologists_share' => $row['tbyb_altkhdyr'],
            'anesthesia' => $row['hs_altkhdyr'],
            'bed' => $row['almbyt'],
            'private' => $row['khas'],
            'done' => $row['tmt_alaamly'],
            'notes' => $row['mlahthat'],
            'notes_2' => $row['mlahthat_thanoy'],
            'displacement_status' => $this->choice($row, ['hal_alnzwh_walskn_wtdrr_albyt', 'displacement_status'], Record::DISPLACEMENT_STATUSES),
            'income_status' => $this->choice($row, ['msadr_aldkhl_walwd_almaly', 'income_status'], Record::INCOME_STATUSES),
            'family_burden' => $this->choice($row, ['hjm_alasr_walhalat_alkhas', 'family_burden'], Record::FAMILY_BURDENS),
            'distributed' => $this->distributed($row),
        ]);
    }

    private function choice(array $row, array $headers, array $options): ?string
    {
        foreach ($headers as $header) {
            if (array_key_exists($header, $row) && $row[$header] !== null && $row[$header] !== '') {
                $value = trim($row[$header]);
                return array_key_exists($value, $options) ? $value : (array_search($value, $options, true) ?: null);
            }
        }

        return null;
    }

    private function distributed(array $row): ?bool
    {
        foreach (['tm_altwzy', 'distributed'] as $header) {
            if (array_key_exists($header, $row) && $row[$header] !== null && $row[$header] !== '') {
                return in_array($row[$header], [1, '1', true, 'نعم', 'yes'], true);
            }
        }

        return null;
    }
}
