import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head, Link, router } from "@inertiajs/react";
import Pagination from "@/Components/Pagination";
import SelectInput from "@/Components/SelectInput";
import TextInput from "@/Components/TextInput";

export default function Show({
    auth,
    connection,
    queries,
    queryParams = null,
}) {
    queryParams = queryParams || {};

    const searchFieldChanged = (name, value) => {
        if (value) {
            queryParams[name] = value;
        } else {
            delete queryParams[name];
        }

        router.get(route("query.index"), queryParams);
    };

    const onKeyPress = (name, e) => {
        if (e.key !== "Enter") return;

        searchFieldChanged(name, e.target.value);
    };

    const deleteQuery = (query) => {
        if (!window.confirm("Are you sure you want to delete the query?")) {
            return;
        }
        router.delete(route("query.destroy", query.id));
    };
    return (
        <AuthenticatedLayout
            user={auth.user}
            header={
                <div className="flex justify-between items-center">
                    <h2 className="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                        {connection} - Queries
                    </h2>
                    <Link
                        // href={route("query.create")}
                        herf="#"
                        className="bg-emerald-500 py-1 px-3 text-white rounded shadow transition-all hover:bg-emerald-600"
                    >
                        Add new
                    </Link>
                </div>
            }
        >
            <Head title={`${connection} - Queries`} />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900 dark:text-gray-100">
                            <pre className="text-white">
                                <div className="overflow-auto">
                                    <table className="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                                        <thead className="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 border-b-2 border-gray-500">
                                            <tr className="text-nowrap">
                                                <th className="px-3 py-3">
                                                    Query Name
                                                </th>
                                                <th className="px-3 py-3">
                                                    Created By
                                                </th>
                                                <th className="px-3 py-3">
                                                    Created At
                                                </th>
                                                <th className="px-3 py-3">
                                                    Updated By
                                                </th>
                                                <th className="px-3 py-3">
                                                    Updated At
                                                </th>
                                                <th className="px-3 py-3">
                                                    Actions
                                                </th>
                                            </tr>
                                        </thead>
                                        <thead className="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 border-b-2 border-gray-500">
                                            <tr className="text-nowrap">
                                                <th className="px-3 py-2">
                                                    <TextInput
                                                        className="w-full"
                                                        defaultValue={
                                                            queryParams.name
                                                        }
                                                        placeholder="Search Query"
                                                        onBlur={(e) =>
                                                            searchFieldChanged(
                                                                "name",
                                                                e.target.value
                                                            )
                                                        }
                                                        onKeyPress={(e) =>
                                                            onKeyPress(
                                                                "name",
                                                                e
                                                            )
                                                        }
                                                    />
                                                </th>
                                                <th className="px-3 py-2">
                                                    <SelectInput
                                                        className="w-full"
                                                        defaultValue={
                                                            queryParams.created_by
                                                        }
                                                        onChange={(e) =>
                                                            searchFieldChanged(
                                                                "created_by",
                                                                e.target.value
                                                            )
                                                        }
                                                    >
                                                        <option value="">
                                                            Select Users
                                                        </option>
                                                        {[
                                                            ...new Set(
                                                                queries.data.map(
                                                                    (query) =>
                                                                        query
                                                                            .created_by
                                                                            .name
                                                                )
                                                            ),
                                                        ].map((name) => (
                                                            <option
                                                                key={name}
                                                                value={name}
                                                            >
                                                                {name}
                                                            </option>
                                                        ))}
                                                    </SelectInput>
                                                </th>
                                                <th className="px-3 py-2"></th>
                                                <th className="px-3 py-2">
                                                    <SelectInput
                                                        className="w-full"
                                                        defaultValue={
                                                            queryParams.updated_by
                                                        }
                                                        onChange={(e) =>
                                                            searchFieldChanged(
                                                                "updated_by",
                                                                e.target.value
                                                            )
                                                        }
                                                    >
                                                        <option value="">
                                                            Select Users
                                                        </option>
                                                        {[
                                                            ...new Set(
                                                                queries.data.map(
                                                                    (query) =>
                                                                        query
                                                                            .updated_by
                                                                            .name
                                                                )
                                                            ),
                                                        ].map((name) => (
                                                            <option
                                                                key={name}
                                                                value={name}
                                                            >
                                                                {name}
                                                            </option>
                                                        ))}
                                                    </SelectInput>
                                                </th>
                                                <th className="px-3 py-2"></th>
                                                <th className="px-3 py-2"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {queries.data.map((query) => (
                                                <tr
                                                    className="bg-white border-b dark:bg-gray-800 dark:border-gray-700"
                                                    key={query.id}
                                                >
                                                    <th className="px-3 py-2 text-white font-bold text-nowrap hover:underline hover:cursor-pointer">
                                                        <Link
                                                            href={route(
                                                                "query.show",
                                                                {
                                                                    id: query.id,
                                                                    client_id:
                                                                        query.client_id,
                                                                }
                                                            )}
                                                        >
                                                            {query.name}
                                                        </Link>
                                                    </th>
                                                    <td className="px-3 py-2">
                                                        {" "}
                                                        {query.created_by.name}
                                                    </td>
                                                    <td className="px-3 py-2">
                                                        {query.created_at}
                                                    </td>
                                                    <td className="px-3 py-2">
                                                        {" "}
                                                        {query.updated_by.name}
                                                    </td>
                                                    <td className="px-3 py-2">
                                                        {query.updated_at}
                                                    </td>
                                                    <td className="px-3 py-2 text-nowrap">
                                                        <Link
                                                            href={route(
                                                                "query.edit",
                                                                query.id
                                                            )}
                                                            className="font-medium text-blue-600 dark:text-blue-500 hover:underline mx-1"
                                                        >
                                                            Edit
                                                        </Link>
                                                        <button
                                                            onClick={(e) =>
                                                                deleteQuery(
                                                                    query
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
                                <Pagination links={queries.meta.links} />

                                {/* {JSON.stringify(queries, null, 2)} */}
                            </pre>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
