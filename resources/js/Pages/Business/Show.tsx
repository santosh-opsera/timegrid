import ManagerDashboard from '@/Pages/Manager/Dashboard';
import PublicBusinessShow from './PublicShow';
import { BusinessShowPageProps } from '@/types/global';

/**
 * Dual-purpose page: renders manager dashboard when `boxes` prop is present,
 * otherwise renders the public business profile.
 */
export default function BusinessShow(props: BusinessShowPageProps) {
    if (props.boxes !== undefined || props.notifications !== undefined) {
        return <ManagerDashboard {...props} />;
    }

    return <PublicBusinessShow {...props} />;
}
