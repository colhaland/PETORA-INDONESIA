<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\DB;

class DocumentNumberService
{
    public static function next(string $docType, string $branchCode, ?string $period = null): string
    {
        $period ??= now()->format('Ym');
        $branchCode = strtoupper($branchCode);
        $docType = strtoupper($docType);

        return DB::transaction(function () use ($docType, $branchCode, $period): string {
            $driver = DB::getDriverName();
            $query = DB::table('document_sequences')->where([
                'doc_type' => $docType,
                'branch_code' => $branchCode,
                'period' => $period,
            ]);
            if ($driver !== 'sqlite') {
                $query->lockForUpdate();
            }
            $row = $query->first();

            if (! $row) {
                DB::table('document_sequences')->insertOrIgnore([
                    'doc_type' => $docType,
                    'branch_code' => $branchCode,
                    'period' => $period,
                    'last_number' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $query = DB::table('document_sequences')->where([
                    'doc_type' => $docType,
                    'branch_code' => $branchCode,
                    'period' => $period,
                ]);
                if ($driver !== 'sqlite') {
                    $query->lockForUpdate();
                }
                $row = $query->first();
            }

            $next = ((int) ($row->last_number ?? 0)) + 1;
            DB::table('document_sequences')->where('id', $row->id)->update([
                'last_number' => $next,
                'updated_at' => now(),
            ]);

            return sprintf('%s/%s/%s/%06d', $docType, $branchCode, $period, $next);
        }, 3);
    }
}
