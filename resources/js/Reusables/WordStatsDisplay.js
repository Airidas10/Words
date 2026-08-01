export function formatAccuracy(accuracy) {
    return Number(accuracy)
}

export function accuracyColorClass(accuracy) {
    if (accuracy === null || accuracy === undefined || Number.isNaN(Number(accuracy))) {
        return 'text-gray-400'
    }

    const value = Number(accuracy)

    if (value <= 0) {
        return 'text-red-600'
    }

    if (value < 50) {
        return 'text-orange-500'
    }

    if (value <= 75) {
        return 'text-blue-600'
    }

    return 'text-green-600'
}
