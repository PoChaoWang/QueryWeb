import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head, Link } from "@inertiajs/react";
import { useState } from "react";
import Modal from "@/Components/Modal";
import axios from "axios";
import TextInput from "@/Components/TextInput";

export default function Index({ auth, connections, tables, success, fail }) {
    const [showModal, setShowModal] = useState(false);
    const [clientToDelete, setClientToDelete] = useState("");
    const [confirmationName, setConfirmationName] = useState("");

    const handleDelete = (client) => {
        setClientToDelete(client);
        setShowModal(true);
    };

    const confirmDelete = async () => {
        if (confirmationName === clientToDelete) {
            try {
                await axios.delete(`/clients/${clientToDelete}`, {
                    data: { confirmation_name: confirmationName },
                });
                location.reload();
            } catch (error) {
                console.error("Failed to delete client:", error);
            }
        } else {
            alert("Database name does not match.");
        }
        setShowModal(false);
        setConfirmationName("");
    };

    const sortedClients = connections.sort();

    const handleSelectConnection = async (client) => {
        try {
            await axios.post("/set-connection", { client });
            window.location.href = `/clients/${client}/queries`;
        } catch (error) {
            console.error("Failed to set connection:", error);
            alert("Failed to set connection. Please try again.");
        }
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={
                <div className="flex justify-between items-center">
                    <h2 className="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                        Clients
                    </h2>

                    <Link
                        href={route("client.create")}
                        className="bg-emerald-500 py-1 px-3 text-white rounded shadow transition-all hover:bg-emerald-600"
                    >
                        Add new
                    </Link>
                </div>
            }
        >
            <Head title="Clients" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    {success && (
                        <div className="bg-emerald-500 py-2 px-4 text-white rounded mb-4">
                            {success}
                        </div>
                    )}
                    {fail && (
                        <div className="bg-red-500 py-2 px-4 text-white rounded mb-4">
                            {fail}
                        </div>
                    )}
                    <div className="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900 dark:text-gray-100">
                            <ul>
                                {sortedClients.map((client) => (
                                    <li
                                        key={client}
                                        className="mb-2 border-b-2 border-gray-500"
                                    >
                                        <div className="flex justify-between">
                                            <button
                                                onClick={() =>
                                                    handleSelectConnection(
                                                        client
                                                    )
                                                }
                                                className="text-blue-500 hover:underline mx-1"
                                            >
                                                {client}
                                            </button>
                                            <button
                                                onClick={() =>
                                                    handleDelete(
                                                        client.replace(
                                                            "client_",
                                                            ""
                                                        )
                                                    )
                                                }
                                                className="font-medium text-red-600 dark:text-red-500 hover:underline mx-1"
                                            >
                                                Delete
                                            </button>
                                        </div>
                                    </li>
                                ))}
                            </ul>
                        </div>
                    </div>
                    {/* <pre className="text-white">
                        {JSON.stringify(tableDetails, null, 2)}
                    </pre> */}
                </div>
            </div>

            <Modal show={showModal} onClose={() => setShowModal(false)}>
                <div className="p-6">
                    <h2 className="text-lg font-semibold text-white">
                        Are you sure you want to delete{" "}
                        <span className="text-2xl font-extrabold text-red-300">
                            {clientToDelete}
                        </span>
                        ?
                    </h2>
                    <p className="text-gray-300">
                        Type the database name to confirm deletion:
                    </p>
                    <TextInput
                        className="w-full mt-2 p-2 border border-gray-300 rounded"
                        value={confirmationName}
                        placeholder="Database Name"
                        onChange={(e) => setConfirmationName(e.target.value)}
                    />
                    <div className="mt-4 flex justify-end">
                        <button
                            onClick={() => setShowModal(false)}
                            className="mr-2 bg-gray-500 text-white p-2 rounded dark:hover:bg-gray-700"
                        >
                            Cancel
                        </button>
                        <button
                            onClick={confirmDelete}
                            className="bg-red-500 text-white p-2 rounded dark:hover:bg-red-700"
                        >
                            Confirm Delete
                        </button>
                    </div>
                </div>
                <pre className="text-white">
                    {/* {(JSON.stringify(tables), null, 2)} */}
                </pre>
            </Modal>
        </AuthenticatedLayout>
    );
}
