import React from "react";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head, useForm, Link } from "@inertiajs/react";
import InputError from "@/Components/InputError";
import InputLabel from "@/Components/InputLabel";
import TextInput from "@/Components/TextInput";

export default function GoogleCreate({ auth, currentDatabase }) {
    const { data, setData, post, errors, reset } = useForm({
        account_id: "",
        account_name: "",
    });

    const onSubmit = (e) => {
        e.preventDefault();
        post(route("google.store"));
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={
                <div className="flex justify-between items-center">
                    <h2 className="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                        New Google Account Creating
                    </h2>
                </div>
            }
        >
            <Head title={`New Google Account Creating`} />
            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="px-6 text-gray-900 dark:text-gray-100">
                            <div className="overflow-auto">
                                <form
                                    onSubmit={onSubmit}
                                    className=" bg-white dark:bg-gray-800 shadow sm:rounded-lg"
                                >
                                    <div className="mt-4">
                                        <InputLabel
                                            htmlFor="account_id"
                                            value="Account ID"
                                        />
                                        <TextInput
                                            id="account_id"
                                            type="text"
                                            name="account_id"
                                            value={data.account_id}
                                            className="mt-1 block w-full"
                                            isFocused={true}
                                            onChange={(e) => {
                                                setData(
                                                    "account_id",
                                                    e.target.value
                                                );
                                            }}
                                        />
                                        <InputError
                                            message={errors.account_id}
                                            className="mt-2"
                                        />
                                    </div>

                                    <div className="mt-4">
                                        <InputLabel
                                            htmlFor="account_name"
                                            value="Account Name"
                                        />
                                        <TextInput
                                            id="account_name"
                                            type="text"
                                            name="account_name"
                                            value={data.account_name}
                                            className="mt-1 block w-full"
                                            isFocused={false}
                                            onChange={(e) => {
                                                setData(
                                                    "account_name",
                                                    e.target.value
                                                );
                                            }}
                                        />
                                        <InputError
                                            message={errors.account_name}
                                            className="mt-2"
                                        />
                                    </div>
                                    <div className="flex items-center justify-start my-4">
                                        <button
                                            type="submit"
                                            className="bg-emerald-500 py-1 px-3 mr-2 text-white rounded shadow transition-all hover:bg-emerald-600"
                                        >
                                            Submit
                                        </button>
                                        <Link
                                            href={route("google.show", {
                                                connections: currentDatabase,
                                            })}
                                            className="bg-gray-300 py-1 px-3 text-gray-700 rounded shadow transition-all hover:bg-gray-400"
                                        >
                                            Cancel
                                        </Link>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
