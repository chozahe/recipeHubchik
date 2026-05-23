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

IMAGE="recipehub-app:${TAG}"

echo "Starting Docker Compose with ${IMAGE}..."
RECIPEHUB_IMAGE="$IMAGE" docker compose up -d
