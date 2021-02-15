<?php

namespace App\Http\Controllers;

use App\Archive;
use App\Employee_names;
use App\GenerateFile\Excel;
use App\Production;
use Carbon\Carbon;
use http\Env\Response;
use Illuminate\Http\Request;

class StatisticsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function statistics()
    {
        $listFile = Archive::query()->select('date', 'namefile');

        // كائن يقوم بإعطاء الوقت
        $carbon = new Carbon();

        // جلب الصف المضاف اليوم فقط
        $data = Production::query()
            ->select(['productions.id_emp', 'productions.production', 'productions.updated_at','employee_names.name', 'employee_names.all_production'])
            ->join('employee_names', 'productions.id_emp', '=', 'employee_names.id_emp')
            ->whereBetween('updated_at',[
                $carbon->toDateString() . ' 00:00:00',
                $carbon->toDateString() . ' 23:59:59'
            ]);

        if($data !== null) {

            $prodArr = $data->get()->count();

            if($prodArr > 0)
            {
                return view('statistics')
                    ->with('prodEmployees', $data->get())
                    ->with('listFile', $listFile->get())
                    ->with('navStatistics', 'active')
                    ->with('isProduct', true);
            }else
            {
                return view('statistics')
                    ->with('navStatistics', 'active')
                    ->with('listFile', $listFile->get())
                    ->with('isProduct', false);
            }
        }
        return view('statistics')
            ->with('navStatistics', 'active')
            ->with('listFile', $listFile->get())
            ->with('isProduct', false);
    }

    public function downloadFileDay()
    {
        $date = new Carbon();

        $prodinfo = Production::query()->selectRaw('id_emp, production, date(updated_at) as date')
            ->whereRaw('updated_at >= ? and updated_at < ? + INTERVAL 1 DAY',[$date->toDateString(), $date->toDateString()])->get();


        if($prodinfo->count() > 0) {
            $file = Excel::generateFileThisDay($prodinfo, 'date_production.xlsx');

            $headers = [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'inline; filename="date_production.xlsx"'
            ];

            return response()->download($file, 'date_production.xlsx', $headers);
        }
        return redirect('statistics')->with('error', 'الملف فارغ، لايوجد بيانات لموظفي الإنتاج اليوم.');
    }

    public function downloadFileMonth()
    {
        // fetch employee
        $emp = Production::query()->select('productions.id_emp','employee_names.name', 'employee_names.all_production')
            ->selectRaw('GROUP_CONCAT(productions.production) as production')
            ->selectRaw('GROUP_CONCAT(DAY(productions.updated_at)) as updated')
            ->join('employee_names','productions.id_emp','=','employee_names.id_emp')
            ->groupBy(['id_emp'])->get();

        // create file excel
        Excel::generateFileThisMonth($emp, 'files/excel/', null);

        $file = public_path() . "/files/excel/this_month_production.xlsx";

        $headers = [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'inline; filename="this_month_production.xlsx"'
        ];

        return response()->download($file, 'this_month_production.xlsx', $headers);
    }

    public function downloadLastFile(Request $request)
    {
        if($request->datefile != null) {
            $path = public_path() . '/files/' . $request->datefile . 'date_production.xlsx';

            return response()->download($path, $request->datefile . 'date_production.xlsx',
                [
                    'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'Content-Disposition' => 'inline; filename="date_production.xlsx"'
                ]);
        }
        return redirect('statistics')->with('error', 'لايوجد ملفات انتاج شهرية حاليا.');
    }
}
