//
//  main.m
//  PhotoAlbum-Diary
//
//  Created by Bin Yang on 6/15/16.
//  Copyright © 2016 Bin Yang. All rights reserved.
//

#import <UIKit/UIKit.h>
#import "AppDelegate.h"

int main(int argc, char * argv[]) {
    @autoreleasepool {
        void MyLib_Log2File(void);
#if !(TARGET_OS_SIMULATOR)
        MyLib_Log2File();
#endif
        return UIApplicationMain(argc, argv, nil, NSStringFromClass([AppDelegate class]));
    }
}
