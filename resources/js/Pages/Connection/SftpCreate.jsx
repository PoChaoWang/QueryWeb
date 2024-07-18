import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head } from "@inertiajs/react";

export default function SftpCreate({ auth }) {
    return (
        <AuthenticatedLayout
            user={auth.user}
            header={
                <div className="flex justify-between items-center">
                    <h2 className="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                        Sftp Creating
                    </h2>
                </div>
            }
        >
            <Head title={`Sftp Creating`} />
            SFTP
        </AuthenticatedLayout>
    );
}
