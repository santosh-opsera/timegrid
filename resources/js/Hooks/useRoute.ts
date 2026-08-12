import { usePage } from '@inertiajs/react';
import { useCallback } from 'react';
import { route as ziggyRoute } from 'ziggy-js';
import { Config as ZiggyConfig } from 'ziggy-js';

type RouteParams = Record<string, unknown> | undefined;

interface RouteHelper {
    current: (name?: string) => boolean;
}

export function useRoute() {
    const { ziggy } = usePage<{ ziggy: ZiggyConfig }>().props;

    const routeFn = useCallback(
        (name?: string, params?: RouteParams, absolute?: boolean): string | RouteHelper => {
            if (name === undefined) {
                return {
                    current: (checkName?: string) => {
                        if (!checkName) return false;
                        try {
                            return ziggyRoute().current(checkName, undefined, undefined, ziggy);
                        } catch {
                            return window.location.pathname.includes(checkName.replace(/\./g, '/'));
                        }
                    },
                };
            }
            return ziggyRoute(name, params, absolute, ziggy);
        },
        [ziggy],
    );

    return routeFn;
}

export default useRoute;
