<?php

namespace App\Http\Controllers;

use App\Events\DashboardWidgetUpdated;
use App\Models\DashboardWidget;
use App\Services\AuditLogService;
use App\Support\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardWidgetController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(DashboardWidget::query()->orderBy('position')->paginate(50));
    }

    public function store(Request $request, AuditLogService $audit): JsonResponse
    {
        $this->authorize('manage', app(TenantContext::class)->get());
        $widget = DashboardWidget::create($request->validate([
            'key' => ['required', 'alpha_dash', 'max:80'],
            'title' => ['required', 'string', 'max:120'],
            'type' => ['required', 'string', 'max:50'],
            'configuration' => ['nullable', 'array'],
            'position' => ['nullable', 'integer', 'min:0', 'max:10000'],
        ]));
        $audit->record('widget.created', $widget);
        DashboardWidgetUpdated::dispatch($widget, 'created');

        return response()->json($widget, 201);
    }

    public function update(Request $request, DashboardWidget $widget, AuditLogService $audit): JsonResponse
    {
        $this->authorize('manage', app(TenantContext::class)->get());
        $widget->update($request->validate([
            'title' => ['sometimes', 'string', 'max:120'],
            'type' => ['sometimes', 'string', 'max:50'],
            'configuration' => ['sometimes', 'nullable', 'array'],
            'position' => ['sometimes', 'integer', 'min:0', 'max:10000'],
        ]));
        $audit->record('widget.updated', $widget);
        DashboardWidgetUpdated::dispatch($widget, 'updated');

        return response()->json($widget->fresh());
    }

    public function destroy(Request $request, DashboardWidget $widget, AuditLogService $audit): JsonResponse
    {
        $this->authorize('manage', app(TenantContext::class)->get());
        $audit->record('widget.deleted', $widget);
        DashboardWidgetUpdated::dispatch($widget, 'deleted');
        $widget->delete();

        return response()->json(status: 204);
    }

}
