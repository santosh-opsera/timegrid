import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Business } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';

interface CalendarEvent {
    id: number;
    title: string;
    start: string;
    end: string;
    color: string;
    status: string;
}

interface Props {
    business: Business;
    events: CalendarEvent[];
}

const WEEKDAYS = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

function toDateKey(date: Date): string {
    return date.toISOString().slice(0, 10);
}

function getMonthDays(year: number, month: number) {
    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    const daysInMonth = lastDay.getDate();
    const startOffset = firstDay.getDay();

    const cells: { date: Date | null; key: string }[] = [];

    for (let i = 0; i < startOffset; i++) {
        cells.push({ date: null, key: `empty-start-${i}` });
    }

    for (let day = 1; day <= daysInMonth; day++) {
        const date = new Date(year, month, day);
        cells.push({ date, key: toDateKey(date) });
    }

    while (cells.length % 7 !== 0) {
        cells.push({ date: null, key: `empty-end-${cells.length}` });
    }

    return cells;
}

export default function Calendar({ business, events }: Props) {
    const today = new Date();
    const [currentMonth, setCurrentMonth] = useState(
        new Date(today.getFullYear(), today.getMonth(), 1),
    );

    const year = currentMonth.getFullYear();
    const month = currentMonth.getMonth();
    const cells = getMonthDays(year, month);

    const eventsByDate = events.reduce<Record<string, CalendarEvent[]>>((acc, event) => {
        const key = event.start.slice(0, 10);
        if (!acc[key]) acc[key] = [];
        acc[key].push(event);
        return acc;
    }, {});

    const goToPrevMonth = () => {
        setCurrentMonth(new Date(year, month - 1, 1));
    };

    const goToNextMonth = () => {
        setCurrentMonth(new Date(year, month + 1, 1));
    };

    const goToAgenda = (date: Date) => {
        router.get(route('businesses.agenda.index', business.slug), {
            date: toDateKey(date),
        });
    };

    const monthLabel = currentMonth.toLocaleDateString(undefined, {
        month: 'long',
        year: 'numeric',
    });

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center justify-between">
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        Calendar — {business.name}
                    </h2>
                    <div className="flex items-center gap-4">
                        <Link
                            href={route('businesses.agenda.index', business.slug)}
                            className="text-sm text-indigo-600 hover:text-indigo-800"
                        >
                            Agenda View
                        </Link>
                        <Link
                            href={route('businesses.show', business.slug)}
                            className="text-sm text-gray-600 hover:text-gray-800"
                        >
                            ← Back to Dashboard
                        </Link>
                    </div>
                </div>
            }
        >
            <Head title={`Calendar — ${business.name}`} />

            <div className="py-8">
                <div className="mx-auto max-w-4xl sm:px-6 lg:px-8">
                    <div className="rounded-lg border border-gray-200 bg-white shadow-sm">
                        <div className="flex items-center justify-between border-b border-gray-200 px-4 py-3">
                            <button
                                onClick={goToPrevMonth}
                                className="rounded-md px-3 py-1 text-sm text-gray-600 hover:bg-gray-100"
                            >
                                ← Prev
                            </button>
                            <h3 className="text-lg font-semibold text-gray-900">{monthLabel}</h3>
                            <button
                                onClick={goToNextMonth}
                                className="rounded-md px-3 py-1 text-sm text-gray-600 hover:bg-gray-100"
                            >
                                Next →
                            </button>
                        </div>

                        <div className="grid grid-cols-7 border-b border-gray-200 bg-gray-50">
                            {WEEKDAYS.map((day) => (
                                <div
                                    key={day}
                                    className="px-2 py-2 text-center text-xs font-medium uppercase text-gray-500"
                                >
                                    {day}
                                </div>
                            ))}
                        </div>

                        <div className="grid grid-cols-7">
                            {cells.map((cell) => {
                                if (!cell.date) {
                                    return (
                                        <div
                                            key={cell.key}
                                            className="min-h-[80px] border-b border-r border-gray-100 bg-gray-50"
                                        />
                                    );
                                }

                                const dateKey = toDateKey(cell.date);
                                const dayEvents = eventsByDate[dateKey] ?? [];
                                const isToday = dateKey === toDateKey(today);

                                return (
                                    <button
                                        key={cell.key}
                                        onClick={() => goToAgenda(cell.date!)}
                                        className={`min-h-[80px] border-b border-r border-gray-100 p-1 text-left transition hover:bg-indigo-50 ${
                                            isToday ? 'bg-indigo-50' : ''
                                        }`}
                                    >
                                        <span
                                            className={`inline-flex h-6 w-6 items-center justify-center rounded-full text-sm ${
                                                isToday
                                                    ? 'bg-indigo-600 font-semibold text-white'
                                                    : 'text-gray-900'
                                            }`}
                                        >
                                            {cell.date.getDate()}
                                        </span>
                                        <div className="mt-1 space-y-0.5">
                                            {dayEvents.slice(0, 3).map((event) => (
                                                <div
                                                    key={event.id}
                                                    className="truncate rounded px-1 text-xs text-white"
                                                    style={{ backgroundColor: event.color }}
                                                    title={event.title}
                                                >
                                                    {event.title}
                                                </div>
                                            ))}
                                            {dayEvents.length > 3 && (
                                                <div className="px-1 text-xs text-gray-500">
                                                    +{dayEvents.length - 3} more
                                                </div>
                                            )}
                                        </div>
                                    </button>
                                );
                            })}
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
