import { SVGAttributes } from 'react';

export default function ApplicationLogo({
    className = 'h-8 w-auto',
    ...props
}: SVGAttributes<SVGElement>) {
    return (
        <svg
            viewBox="0 0 48 48"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
            className={className}
            aria-hidden="true"
            {...props}
        >
            <rect width="48" height="48" rx="12" className="fill-brand-600" />
            <path
                d="M14 16h20v4H14v-4zm0 8h20v2H14v-2zm0 6h14v2H14v-2z"
                className="fill-white"
            />
            <circle cx="34" cy="30" r="6" className="fill-blue-400" />
            <path
                d="M32 30h4M34 28v4"
                stroke="white"
                strokeWidth="1.5"
                strokeLinecap="round"
            />
        </svg>
    );
}
