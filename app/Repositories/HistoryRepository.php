<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\History;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class HistoryRepository
{
    public function create(int $workerId, int $machineId, int $cycleId): ?History
    {
        return History::create([
            'worker_id' => $workerId,
            'machine_id' => $machineId,
            'cycle_id' => $cycleId,
        ]);
    }

    public function machineHistory(int $machineId): ?Collection
    {
        return History::join('machines', 'machines.id', 'histories.machine_id')
            ->join('cycles', 'cycles.id', 'histories.cycle_id')
            ->join('workers', 'workers.id', 'histories.worker_id')
            ->where('machines.id', $machineId)
            ->select(
                'workers.name as worker',
                'cycles.created_at as start',
                'cycles.updated_at as end',
                'cycles.complete'
            )
            ->orderBy('cycles.id', 'desc')
            ->get();
    }

    public function workerHistory(string $workerName): LengthAwarePaginator
    {
        return History::join('workers', 'workers.id', 'histories.worker_id')
            ->join('cycles', 'cycles.id', 'histories.cycle_id')
            ->join('machines', 'machines.id', 'histories.machine_id')
            ->where('workers.name', $workerName)
            ->select(
                'machines.id as machine',
                'cycles.created_at as start',
                'cycles.updated_at as end',
                'cycles.complete'
            )
            ->orderBy('cycles.id', 'desc')
            ->paginate(request('per_page'));
    }

    public function cycleIdToUse(int $machineId, string $workerName): ?History
    {
        return History::join('machines', 'machines.id', 'histories.machine_id')
            ->join('cycles', 'cycles.id', 'histories.cycle_id')
            ->join('workers', 'workers.id', 'histories.worker_id')
            ->where('machines.id', $machineId)
            ->where('workers.name', $workerName)
            ->where('cycles.complete', false)
            ->select('cycles.id')
            ->first();
    }
}
