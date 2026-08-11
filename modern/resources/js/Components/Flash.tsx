import { usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import { PageProps } from '@/types';

export default function Flash() {
    const { flash } = usePage<PageProps>().props;
    const [visible, setVisible] = useState(false);
    const [message, setMessage] = useState<{ type: 'success' | 'error'; text: string } | null>(null);

    useEffect(() => {
        if (flash?.success) {
            setMessage({ type: 'success', text: flash.success });
            setVisible(true);
        } else if (flash?.error) {
            setMessage({ type: 'error', text: flash.error });
            setVisible(true);
        } else {
            setVisible(false);
            setMessage(null);
        }
    }, [flash?.success, flash?.error]);

    useEffect(() => {
        if (!visible) {
            return;
        }

        const timer = setTimeout(() => setVisible(false), 3000);
        return () => clearTimeout(timer);
    }, [visible, message]);

    if (!visible || !message) {
        return null;
    }

    const colors =
        message.type === 'success'
            ? 'bg-green-50 text-green-800 border-green-200'
            : 'bg-red-50 text-red-800 border-red-200';

    return (
        <div className={`border-b px-4 py-3 text-sm ${colors}`}>
            <div className="mx-auto max-w-7xl">{message.text}</div>
        </div>
    );
}
