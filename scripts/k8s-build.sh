#!/usr/bin/env bash

set -euo pipefail

usage() {
    echo "Usage: $0 -t <tag>" >&2
}

TAG=""

while getopts ":t:h" opt; do
    case "$opt" in
        t)
            TAG="$OPTARG"
            ;;
        h)
            usage
            exit 0
            ;;
        :)
            echo "Option -$OPTARG requires an argument." >&2
            usage
            exit 1
            ;;
        \?)
            echo "Unknown option: -$OPTARG" >&2
            usage
            exit 1
            ;;
    esac
done

if [ -z "$TAG" ]; then
    echo "Image tag is required." >&2
    usage
    exit 1
fi

command -v minikube >/dev/null 2>&1 || {
    echo "minikube is required." >&2
    exit 1
}

if ! minikube status >/dev/null 2>&1; then
    echo "Minikube profile is not running or does not exist." >&2
    echo "Start it first, for example:" >&2
    echo "  minikube start --driver=docker --cpus=4 --memory=4096" >&2
    exit 1
fi

IMAGE="recipehub-app:${TAG}"

echo "Building ${IMAGE}..."
docker build -t "$IMAGE" .

echo "Loading ${IMAGE} into Minikube..."
minikube image load "$IMAGE"
