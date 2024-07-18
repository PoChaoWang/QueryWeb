import React from "react";
import { useForm } from "@inertiajs/react";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head } from "@inertiajs/react";
import DatePicker from "react-datepicker";
import "react-datepicker/dist/react-datepicker.css";
import Select from "react-select";

export default function GoogleEdit({
    auth,
    connections,
    accounts,
    reportTypes,
    success,
    fail,
}) {
    const { data, setData, post, processing, errors } = useForm({
        account_names: [],
        report_types: [],
        start_date: null,
        end_date: null,
    });

    const handleSubmit = (e) => {
        e.preventDefault();

        const accountNames = data.account_names.map((a) => a.label);
        const reportTypes = data.report_types.map((r) => r.value);

        const formData = {
            account_names: accountNames,
            report_types: reportTypes,
            start_date: data.start_date,
            end_date: data.end_date,
        };

        console.log("Submitting data:", formData);

        post(route("google.update", { connections: connections }), formData);
    };

    // 為 react-select 準備選項
    const accountOptions = accounts.map((account) => ({
        value: account.id,
        label: account.account_name,
    }));

    const reportTypeOptions = reportTypes.map((type) => ({
        value: type,
        label: type.charAt(0).toUpperCase() + type.slice(1),
    }));

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={
                <div className="flex justify-between items-center ">
                    <h2 className="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                        Update Google Ads Report
                    </h2>
                </div>
            }
        >
            <Head title="Update Google Ads Report" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    {success && (
                        <div
                            className="mb-4 p-4 text-sm text-red-700 bg-red-100 rounded-lg"
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
                    <div className="bg-white dark:bg-gray-800 overflow-visible shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900 dark:text-gray-100 overflow-visible">
                            <form onSubmit={handleSubmit}>
                                <div className="flex space-x-4 gap-4 mb-4">
                                    <div className="w-1/4">
                                        <label
                                            htmlFor="account_names"
                                            className="block mb-2 text-sm font-medium "
                                        >
                                            Google Ads Accounts
                                        </label>
                                        <Select
                                            id="account_names"
                                            isMulti
                                            options={accountOptions}
                                            value={data.account_names}
                                            onChange={(selectedOptions) =>
                                                setData(
                                                    "account_names",
                                                    selectedOptions
                                                )
                                            }
                                            className="basic-multi-select text-gray-900"
                                            classNamePrefix="select"
                                        />
                                        {errors.account_names && (
                                            <div className="text-red-500">
                                                {errors.account_names}
                                            </div>
                                        )}
                                    </div>

                                    <div className="w-1/4">
                                        <label
                                            htmlFor="report_types"
                                            className="block mb-2 text-sm font-medium"
                                        >
                                            Report Types
                                        </label>
                                        <Select
                                            id="report_types"
                                            isMulti
                                            options={reportTypeOptions}
                                            value={data.report_types}
                                            onChange={(selectedOptions) =>
                                                setData(
                                                    "report_types",
                                                    selectedOptions
                                                )
                                            }
                                            className="basic-multi-select text-gray-900"
                                            classNamePrefix="select"
                                        />
                                        {errors.report_types && (
                                            <div className="text-red-500">
                                                {errors.report_types}
                                            </div>
                                        )}
                                    </div>

                                    <div className="w-1/6">
                                        <label
                                            htmlFor="start_date"
                                            className="block mb-2 text-sm font-medium"
                                        >
                                            Start Date
                                        </label>
                                        <DatePicker
                                            id="start_date"
                                            selected={
                                                data.start_date
                                                    ? new Date(data.start_date)
                                                    : null
                                            }
                                            onChange={(date) =>
                                                setData(
                                                    "start_date",
                                                    date
                                                        ? date
                                                              .toISOString()
                                                              .split("T")[0]
                                                        : null
                                                )
                                            }
                                            className="w-full px-3 py-2 text-gray-700 border rounded-lg focus:outline-none"
                                            dateFormat="yyyy-MM-dd"
                                        />

                                        {errors.start_date && (
                                            <div className="text-red-500">
                                                {errors.start_date}
                                            </div>
                                        )}
                                    </div>

                                    <div className="w-1/6">
                                        <label
                                            htmlFor="end_date"
                                            className="block mb-2 text-sm font-medium"
                                        >
                                            End Date
                                        </label>
                                        <DatePicker
                                            id="end_date"
                                            selected={
                                                data.end_date
                                                    ? new Date(data.end_date)
                                                    : null
                                            }
                                            minDate={
                                                data.start_date
                                                    ? new Date(data.start_date)
                                                    : null
                                            }
                                            onChange={(date) =>
                                                setData(
                                                    "end_date",
                                                    date
                                                        ? date
                                                              .toISOString()
                                                              .split("T")[0]
                                                        : null
                                                )
                                            }
                                            className="w-full px-3 py-2 text-gray-700 border rounded-lg focus:outline-none"
                                            dateFormat="yyyy-MM-dd"
                                        />
                                        {errors.end_date && (
                                            <div className="text-red-500">
                                                {errors.end_date}
                                            </div>
                                        )}
                                    </div>
                                    <div className="w-1/6 flex flex-end">
                                        <button
                                            type="submit"
                                            className="px-4 py-2 text-white bg-blue-500 rounded-lg hover:bg-blue-600 focus:outline-none"
                                            disabled={processing}
                                        >
                                            {processing
                                                ? "Processing..."
                                                : "Run Report"}
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
