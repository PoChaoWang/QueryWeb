import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head, Link, router } from "@inertiajs/react";
import { useState } from "react";

export default function MetaShow({
    auth,
    currentDatabase,
    metaAccounts,
    success,
    fail,
}) {
    const [metaAccountsList, setMetaAccountsList] = useState(metaAccounts);

    const deleteMetaAccount = (account_id) => {
        if (!window.confirm("Are you sure you want to delete this account?")) {
            return;
        }
        router.delete(
            route("meta.destroy", {
                account_id: account_id,
                connections: currentDatabase,
            })
        );
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={
                <h2 className="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    {currentDatabase} - Connections
                </h2>
            }
        >
            <Head title={`${currentDatabase} - Connections`} />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    {success && (
                        <div
                            className="mb-4 p-4 text-sm text-green-700 bg-green-100 rounded-lg"
                            role="alert"
                        >
                            {success}
                        </div>
                    )}
                    {fail && (
                        <div
                            className="mb-4 p-4 text-sm text-red-700 bg-red-100 rounded-lg"
                            role="alert"
                        >
                            {fail}
                        </div>
                    )}
                    <div className="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900 dark:text-gray-100">
                            <div className="flex justify-between items-center mb-4">
                                <h3>Meta Accounts</h3>
                                <Link
                                    href={route("meta.create", {
                                        client: currentDatabase,
                                    })}
                                    className="bg-emerald-500 py-1 px-3 text-white rounded shadow transition-all hover:bg-emerald-600"
                                >
                                    Add new Meta account
                                </Link>
                            </div>

                            <div className="overflow-auto">
                                <table className="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                                    <thead className="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 border-b-2 border-gray-500">
                                        <tr className="text-nowrap">
                                            <th className="px-3 py-3">
                                                Account ID
                                            </th>
                                            <th className="px-3 py-3">
                                                Account Name
                                            </th>
                                            <th className="px-3 py-3">
                                                Created At
                                            </th>
                                            <th className="px-3 py-3">
                                                Actions
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {metaAccountsList.map((account) => (
                                            <tr
                                                className="bg-white border-b dark:bg-gray-800 dark:border-gray-700"
                                                key={account.account_id}
                                            >
                                                <td className="px-3 py-2">
                                                    {account.account_id}
                                                </td>
                                                <td className="px-3 py-2">
                                                    {account.account_name}
                                                </td>
                                                <td className="px-3 py-2">
                                                    {account.created_at}
                                                </td>
                                                <td className="px-3 py-2 text-nowrap">
                                                    <button
                                                        onClick={() =>
                                                            deleteMetaAccount(
                                                                account.account_id
                                                            )
                                                        }
                                                        className="font-medium text-red-600 dark:text-red-500 hover:underline mx-1"
                                                    >
                                                        Delete
                                                    </button>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
