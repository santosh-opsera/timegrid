import { Card, PageHeader } from '@/Components/UI';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import useRoute from '@/Hooks/useRoute';
import { ManagerCalendarPageProps } from '@/types/global';
import { Head, Link } from '@inertiajs/react';
import {
    ChevronLeftIcon,
    ChevronRightIcon,
} from '@heroicons/react/24/outline';
import {
    addDays,
    addWeeks,
    eachDayOfInterval,
    format,
    isSameDay,
    parseISO,
    startOfWeek,
    subWeeks,
} from 'date-fns';
import { useMemo, useState } from 'react';

const HOURS = Array.from({ length: 12 }, (_, i) => i + 8); // 8 AM to 7 PM

export default function ManagerCalendar({
    business,
    calendarData,
    icalURL,
}: ManagerCalendarPageProps) {
    const route = useRoute();
    const [currentWeek, setCurrentWeek] = useState(new Date());

    const weekStart = startOfWeek(currentWeek, { weekStartsOn: 1 });
    const weekDays = eachDayOfInterval({ start: weekStart, end: addDays(weekStart, 6) });

    const eventsByDay = useMemo(() => {
        const map: Record<string, typeof calendarData.events> = {};
        calendarData.events.forEach((event) => {
            const day = format(parseISO(event.start), 'yyyy-MM-dd');
            if (!map[day]) map[day] = [];
            map[day].push(event);
        });
        return map;
    }, [calendarData.events]);

    const getEventStyle = (event: (typeof calendarData.events)[0]) => {
        const start = parseISO(event.start);
        const end = parseISO(event.end);
        const startHour = start.getHours() + start.getMinutes() / 60;
        const duration = (end.getTime() - start.getTime()) / (1000 * 60 * 60);
        const top = ((startHour - 8) / 12) * 100;
        const height = (duration / 12) * 100;
        return { top: `${top}%`, height: `${Math.max(height, 8)}%`, backgroundColor: event.color ?? '#4f46e5' };
    };

    return (
        <AuthenticatedLayout
            breadcrumbs={[
                { label: business.name, href: route('manager.business.show', { business: business.slug }) as string },
                { label: 'Calendar' },
            ]}
        >
            <Head title={`Calendar — ${business.name}`} />
            <PageHeader
                title="Calendar"
                description="View and manage your appointment schedule"
                action={
                    <a
                        href={icalURL}
                        className="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"
                    >
                        Export iCal
                    </a>
                }
            />

            <Card padding={false}>
                {/* Week navigation */}
                <div className="flex items-center justify-between border-b border-slate-200 px-6 py-4 dark:border-slate-800">
                    <button
                        type="button"
                        onClick={() => setCurrentWeek(subWeeks(currentWeek, 1))}
                        className="rounded-lg p-2 hover:bg-slate-100 dark:hover:bg-slate-800"
                        aria-label="Previous week"
                    >
                        <ChevronLeftIcon className="h-5 w-5" />
                    </button>
                    <h2 className="text-lg font-semibold text-slate-900 dark:text-white">
                        {format(weekStart, 'MMM d')} – {format(addDays(weekStart, 6), 'MMM d, yyyy')}
                    </h2>
                    <button
                        type="button"
                        onClick={() => setCurrentWeek(addWeeks(currentWeek, 1))}
                        className="rounded-lg p-2 hover:bg-slate-100 dark:hover:bg-slate-800"
                        aria-label="Next week"
                    >
                        <ChevronRightIcon className="h-5 w-5" />
                    </button>
                </div>

                {/* Calendar grid */}
                <div className="overflow-x-auto">
                    <div className="grid min-w-[800px]" style={{ gridTemplateColumns: '60px repeat(7, 1fr)' }}>
                        {/* Header row */}
                        <div className="border-b border-r border-slate-200 dark:border-slate-800" />
                        {weekDays.map((day) => (
                            <div
                                key={day.toISOString()}
                                className={`border-b border-r border-slate-200 px-2 py-3 text-center dark:border-slate-800 ${
                                    isSameDay(day, new Date()) ? 'bg-brand-50 dark:bg-brand-950/30' : ''
                                }`}
                            >
                                <div className="text-xs font-medium text-slate-500">{format(day, 'EEE')}</div>
                                <div className={`text-lg font-semibold ${isSameDay(day, new Date()) ? 'text-brand-600' : 'text-slate-900 dark:text-white'}`}>
                                    {format(day, 'd')}
                                </div>
                            </div>
                        ))}

                        {/* Time grid */}
                        <div className="relative border-r border-slate-200 dark:border-slate-800">
                            {HOURS.map((hour) => (
                                <div key={hour} className="flex h-16 items-start border-b border-slate-100 pt-1 pr-2 text-right text-xs text-slate-400 dark:border-slate-800">
                                    {format(new Date().setHours(hour, 0), 'h a')}
                                </div>
                            ))}
                        </div>

                        {weekDays.map((day) => {
                            const dayKey = format(day, 'yyyy-MM-dd');
                            const dayEvents = eventsByDay[dayKey] ?? [];

                            return (
                                <div key={dayKey} className="relative border-r border-slate-200 dark:border-slate-800">
                                    {HOURS.map((hour) => (
                                        <div key={hour} className="h-16 border-b border-slate-100 dark:border-slate-800/50" />
                                    ))}
                                    {dayEvents.map((event, i) => (
                                        <div
                                            key={`${event.start}-${i}`}
                                            className="absolute inset-x-1 overflow-hidden rounded-lg px-2 py-1 text-xs font-medium text-white shadow-sm"
                                            style={getEventStyle(event)}
                                            title={event.title}
                                        >
                                            <span className="line-clamp-2">{event.title}</span>
                                        </div>
                                    ))}
                                </div>
                            );
                        })}
                    </div>
                </div>
            </Card>
        </AuthenticatedLayout>
    );
}
