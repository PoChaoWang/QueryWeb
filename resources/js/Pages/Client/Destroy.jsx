import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head } from "@inertiajs/react";
import { useState } from "react";
import Modal from "@/Components/Modal"; // 假设您有一个 Modal 组件

export default function Index({ auth, tableDetails }) {
    const [showModal, setShowModal] = useState(false);
    const [clientToDelete, setClientToDelete] = useState("");
    const [confirmationName, setConfirmationName] = useState("");

    const handleDelete = async (client) => {
        setClientToDelete(client);
        setShowModal(true);
    };

    const confirmDelete = async () => {
        if (confirmationName === clientToDelete) {
            await axios.delete(`/clients/${clientToDelete}`, {
                data: { confirmation_name: confirmationName },
            });
            location.reload();
        } else {
            alert("Database name does not match.");
        }
        setShowModal(false);
        setConfirmationName("");
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={
                <div className="flex justify-between items-center">
                    <h2 className="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                        Clients
                    </h2>
                </div>
            }
        >
            <Head title="Clients" />

            <div className="py-12">
                <div className="mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900 dark:text-gray-100">
                            {Object.keys(tableDetails).map(
                                (connection, index) => (
                                    <div key={index} className="mb-4">
                                        <a
                                            href={`/queries/${connection}`}
                                            className="text-blue-500"
                                        >
                                            {connection}
                                        </a>
                                        <button
                                            onClick={() =>
                                                handleDelete(
                                                    connection.replace(
                                                        "mysql_client_",
                                                        ""
                                                    )
                                                )
                                            }
                                            className="text-red-500 ml-4"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                )
                            )}
                        </div>
                    </div>
                </div>
            </div>

            <Modal show={showModal} onClose={() => setShowModal(false)}>
                <div className="p-6">
                    <h2 className="text-lg font-semibold">Confirm Delete</h2>
                    <p>Type the database name to confirm deletion:</p>
                    <input
                        type="text"
                        value={confirmationName}
                        onChange={(e) => setConfirmationName(e.target.value)}
                        className="mt-2 p-2 border border-gray-300 rounded"
                    />
                    <button
                        onClick={confirmDelete}
                        className="mt-4 bg-red-500 text-white p-2 rounded"
                    >
                        Confirm Delete
                    </button>
                </div>
            </Modal>
        </AuthenticatedLayout>
    );
}
