import { useState, useCallback } from 'react';

export function useNativeDragAndDrop() {
    const [draggedItem, setDraggedItem] = useState(null);
    const [draggedSource, setDraggedSource] = useState(null);
    const [isDraggingModalItem, setIsDraggingModalItem] = useState(false);

    const handleDragStart = useCallback((itemData, source = null, isModal = false) => {
        setDraggedItem(itemData);
        setDraggedSource(source);
        setIsDraggingModalItem(isModal);
    }, []);

    const handleDragEnd = useCallback(() => {
        setDraggedItem(null);
        setDraggedSource(null);
        setIsDraggingModalItem(false);
    }, []);

    return {
        draggedItem,
        draggedSource,
        isDraggingModalItem,
        handleDragStart,
        handleDragEnd
    };
}
