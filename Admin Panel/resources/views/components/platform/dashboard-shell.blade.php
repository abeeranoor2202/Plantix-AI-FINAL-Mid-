@props([
    'title' => 'Dashboard',
    'subtitle' => null,
    'summaryCards' => [],
    'alerts' => [],
    'recentActivity' => [],
    'pendingActions' => [],
    'activityTitle' => 'Recent Activity',
    'pendingTitle' => 'Pending Actions',
    'activityEmptyText' => 'No recent activity found.',
    'pendingEmptyText' => 'No pending actions.',
])

<section class="platform-dashboard-shell" style="margin-bottom: 28px; display: grid; gap: 18px;">
    <div class="platform-dashboard-head" style="display: flex; justify-content: space-between; align-items: flex-end; gap: 12px; flex-wrap: wrap;">
        <div>
            <h1 style="margin: 0; font-size: 28px; line-height: 1.15;">{{ $title }}</h1>
            @if($subtitle)
                <p style="margin: 8px 0 0 0; color: var(--panel-muted);">{{ $subtitle }}</p>
            @endif
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px;">
        @foreach($summaryCards as $card)
            @if(!empty($card['href']))
                <a href="{{ $card['href'] }}" class="dashboard-summary-card" style="text-decoration: none; color: inherit; display: block;">
                    <x-card bodyClass="p-0" class="h-100">
                        <div style="padding: 18px 20px; min-height: 112px; display: flex; align-items: center;">
                            <div style="display: flex; justify-content: space-between; align-items: center; gap: 12px; width: 100%;">
                                <div>
                                    <div style="font-size: 13px; color: var(--panel-muted); font-weight: 600;">{{ $card['label'] ?? '' }}</div>
                                    <div style="font-size: 28px; font-weight: 800; line-height: 1.1; margin-top: 6px; color: var(--panel-text);">{{ $card['value'] ?? '-' }}</div>
                                    @if(!empty($card['hint']))
                                        <div style="font-size: 12px; color: var(--panel-muted); margin-top: 6px;">{{ $card['hint'] }}</div>
                                    @endif
                                </div>
                                <div style="width: 44px; height: 44px; border-radius: 14px; background: var(--panel-primary-soft); display: flex; align-items: center; justify-content: center; color: var(--panel-primary-dark); flex: 0 0 auto;">
                                    <i class="{{ $card['icon'] ?? 'fas fa-chart-line' }}"></i>
                                </div>
                            </div>
                        </div>
                    </x-card>
                </a>
            @else
                <x-card bodyClass="p-0" class="h-100">
                    <div style="padding: 18px 20px; min-height: 112px; display: flex; align-items: center;">
                        <div style="display: flex; justify-content: space-between; align-items: center; gap: 12px; width: 100%;">
                            <div>
                                <div style="font-size: 13px; color: var(--panel-muted); font-weight: 600;">{{ $card['label'] ?? '' }}</div>
                                <div style="font-size: 28px; font-weight: 800; line-height: 1.1; margin-top: 6px; color: var(--panel-text);">{{ $card['value'] ?? '-' }}</div>
                                @if(!empty($card['hint']))
                                    <div style="font-size: 12px; color: var(--panel-muted); margin-top: 6px;">{{ $card['hint'] }}</div>
                                @endif
                            </div>
                            <div style="width: 44px; height: 44px; border-radius: 14px; background: var(--panel-primary-soft); display: flex; align-items: center; justify-content: center; color: var(--panel-primary-dark); flex: 0 0 auto;">
                                <i class="{{ $card['icon'] ?? 'fas fa-chart-line' }}"></i>
                            </div>
                        </div>
                    </div>
                </x-card>
            @endif
        @endforeach
    </div>

    @if(!empty($alerts))
        <x-card>
            <div style="padding: 14px 20px; border-bottom: 1px solid var(--panel-border); display:flex; align-items:center; justify-content:space-between; gap: 10px;">
                <h3 style="font-size: 17px; margin: 0;">System Alerts</h3>
                <span class="badge bg-danger">{{ count($alerts) }}</span>
            </div>
            <div style="padding: 12px 20px; display: grid; gap: 10px;">
                @foreach($alerts as $alert)
                    <div style="display:flex; justify-content: space-between; align-items:center; gap: 10px; border: 1px solid var(--panel-border); border-radius: 12px; padding: 10px 12px;">
                        <div>
                            <div style="font-weight: 700; color: var(--panel-text);">{{ $alert['label'] ?? 'Alert' }}</div>
                            <small class="text-muted">{{ strtoupper($alert['level'] ?? 'medium') }} priority</small>
                        </div>
                        <span class="badge {{ ($alert['level'] ?? 'medium') === 'high' ? 'bg-danger' : 'bg-warning text-dark' }}">{{ $alert['count'] ?? 0 }}</span>
                    </div>
                @endforeach
            </div>
        </x-card>
    @endif

    <div style="display: grid; grid-template-columns: minmax(0, 2fr) minmax(300px, 1fr); gap: 16px; align-items: start;">
        <x-card>
            <div style="padding: 16px 20px; border-bottom: 1px solid var(--panel-border); display: flex; align-items: center; justify-content: space-between; gap: 12px;">
                <h3 style="font-size: 17px; margin: 0;">{{ $activityTitle }}</h3>
            </div>
            <div style="padding: 8px 0;">
                @forelse($recentActivity as $activity)
                    @if(!empty($activity['href']))
                        <a href="{{ $activity['href'] }}" style="display: grid; grid-template-columns: minmax(140px, 170px) 1fr; gap: 8px; align-items: center; padding: 12px 20px; border-bottom: 1px solid var(--panel-border); text-decoration: none; color: inherit; transition: background 0.15s ease;">
                            <div style="font-size: 12px; color: var(--panel-muted);">{{ $activity['time'] ?? '-' }}</div>
                            <div>
                                <div style="font-weight: 700; color: var(--panel-text);">{{ $activity['title'] ?? '-' }}</div>
                                @if(!empty($activity['meta']))
                                    <small class="text-muted">{{ $activity['meta'] }}</small>
                                @endif
                            </div>
                        </a>
                    @else
                        <div style="display: grid; grid-template-columns: minmax(140px, 170px) 1fr; gap: 8px; align-items: center; padding: 12px 20px; border-bottom: 1px solid var(--panel-border);">
                            <div style="font-size: 12px; color: var(--panel-muted);">{{ $activity['time'] ?? '-' }}</div>
                            <div>
                                <div style="font-weight: 700; color: var(--panel-text);">{{ $activity['title'] ?? '-' }}</div>
                                @if(!empty($activity['meta']))
                                    <small class="text-muted">{{ $activity['meta'] }}</small>
                                @endif
                            </div>
                        </div>
                    @endif
                @empty
                    <div style="padding: 20px; color: var(--panel-muted);">{{ $activityEmptyText }}</div>
                @endforelse
            </div>
        </x-card>

        <x-card>
            <div style="padding: 16px 20px; border-bottom: 1px solid var(--panel-border);">
                <h3 style="font-size: 17px; margin: 0;">{{ $pendingTitle }}</h3>
            </div>
            <div style="padding: 10px 20px; display: grid; gap: 10px;">
                @forelse($pendingActions as $item)
                    <a href="{{ $item['href'] ?? '#' }}" style="display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 10px 12px; border: 1px solid var(--panel-border); border-radius: 12px; text-decoration: none; color: inherit;">
                        <span style="font-weight: 600;">{{ $item['label'] ?? '-' }}</span>
                        <span class="badge bg-warning text-dark">{{ $item['count'] ?? 0 }}</span>
                    </a>
                @empty
                    <div style="color: var(--panel-muted);">{{ $pendingEmptyText }}</div>
                @endforelse
            </div>
        </x-card>
    </div>
</section>
