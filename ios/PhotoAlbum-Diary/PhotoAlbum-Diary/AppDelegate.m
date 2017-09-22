//
//  AppDelegate.m
//  PhotoAlbum-Diary
//
//  Created by Bin Yang on 6/15/16.
//  Copyright © 2016 Bin Yang. All rights reserved.
//

#import "AppDelegate.h"

BOOL g_wakeupByUrlSession = NO;

@interface AppDelegate ()

//@property MyLib *myLocation;


@end


@implementation AppDelegate


- (BOOL)application:(UIApplication *)application didFinishLaunchingWithOptions:(NSDictionary *)launchOptions {
    // Override point for customization after application launch.
    MyInf(@"didFinishLaunchingWithOptions: launchOptions %@", launchOptions);

//    _myLocation = [[MyLib alloc] init];

    if (launchOptions) {
        __block UIBackgroundTaskIdentifier bgTask = [[UIApplication sharedApplication] beginBackgroundTaskWithName:@"MyTask" expirationHandler:^{
            // Clean up any unfinished task business by marking where you
            // stopped or ending the task outright.]
            MyInf(@"expirationHandle ...");
            MyInf(@"Time Remaining: %f", [[UIApplication sharedApplication] backgroundTimeRemaining]);
            [[UIApplication sharedApplication] endBackgroundTask:bgTask];
            bgTask = UIBackgroundTaskInvalid;
        }];
        MyInf(@"Time Remaining: %f", [[UIApplication sharedApplication] backgroundTimeRemaining]);
    }
    
    AlbumSync_init();

//    MyInf(@"UIApplicationLaunchOptionsLocationKey : %@" , [launchOptions objectForKey:UIApplicationLaunchOptionsLocationKey]);
//    if ([launchOptions objectForKey:UIApplicationLaunchOptionsLocationKey]) {
    if (launchOptions) {
        [defaultAlbumSync startLocationMonitor];
        
        [defaultAlbumSync startLocationUpdate];
        dispatch_queue_t checkQueue = dispatch_queue_create("checkQueue", DISPATCH_QUEUE_SERIAL);
        dispatch_async(checkQueue, ^{
            //TODO: check if upload pending, sleep 600 for WIFI waiting */
            sleep(600);
            while(true) {
                MyInf(@"sleep 10 ...");
                sleep(10);
                MyInf(@"check upload state");
                if (!defaultAlbumSync.uploadOnGoing) {
                    MyInf(@"uploading not ongoing, stop location update");
                    [defaultAlbumSync stopLocationUpdate];
                    break;
                }
            }
        });
    }

/*
    if (launchOptions) {
        __block UIBackgroundTaskIdentifier bgTask = [[UIApplication sharedApplication] beginBackgroundTaskWithName:@"MyTask" expirationHandler:^{
            // Clean up any unfinished task business by marking where you
            // stopped or ending the task outright.]
            MyInf(@"expirationHandle ...");
            MyInf(@"Time Remaining: %f", [[UIApplication sharedApplication] backgroundTimeRemaining]);
            [[UIApplication sharedApplication] endBackgroundTask:bgTask];
            bgTask = UIBackgroundTaskInvalid;
        }];
        MyInf(@"Time Remaining: %f", [[UIApplication sharedApplication] backgroundTimeRemaining]);
    }
*/

    return YES;
}

- (void)application:(UIApplication *)application handleEventsForBackgroundURLSession:(NSString *)identifier
  completionHandler:(void (^)())completionHandler
{
    MyInf(@"application handleEventsForBackgroundURLSession\n");
    /*
     Store the completion handler. The completion handler is invoked by the view controller's checkForAllDownloadsHavingCompleted method (if all the download tasks have been completed).
     */
}

- (void)applicationWillResignActive:(UIApplication *)application {
    // Sent when the application is about to move from active to inactive state. This can occur for certain types of temporary interruptions (such as an incoming phone call or SMS message) or when the user quits the application and it begins the transition to the background state.
    // Use this method to pause ongoing tasks, disable timers, and throttle down OpenGL ES frame rates. Games should use this method to pause the game.
    MyInf(@"Will enter inactive");
    [defaultAlbumSync startLocationUpdate];
    [defaultAlbumSync startLocationMonitor];
    dispatch_queue_t checkQueue = dispatch_queue_create("checkQueue", DISPATCH_QUEUE_SERIAL);
    dispatch_async(checkQueue, ^{
        while(true) {
            MyInf(@"sleep 10 ...");
            sleep(10);
            MyInf(@"check upload state");
            if (!defaultAlbumSync.uploadOnGoing) {
                MyInf(@"uploading not ongoing, stop location update");
                [defaultAlbumSync stopLocationUpdate];
                break;
            }
        }
        });
}

- (void)applicationDidEnterBackground:(UIApplication *)application {
    // Use this method to release shared resources, save user data, invalidate timers, and store enough application state information to restore your application to its current state in case it is terminated later.
    // If your application supports background execution, this method is called instead of applicationWillTerminate: when the user quits.
    MyInf(@"Did enter backgroud");

    /*
    __block UIBackgroundTaskIdentifier bgTask = [[UIApplication sharedApplication] beginBackgroundTaskWithName:@"MyTask" expirationHandler:^{
        // Clean up any unfinished task business by marking where you
        // stopped or ending the task outright.]
        MyInf(@"expirationHandle ...");
        MyInf(@"Time Remaining: %f", [[UIApplication sharedApplication] backgroundTimeRemaining]);
        [[UIApplication sharedApplication] endBackgroundTask:bgTask];
        bgTask = UIBackgroundTaskInvalid;
    }];
    MyInf(@"Time Remaining: %f", [[UIApplication sharedApplication] backgroundTimeRemaining]);
*/

}

- (void)applicationWillEnterForeground:(UIApplication *)application {
    // Called as part of the transition from the background to the inactive state; here you can undo many of the changes made on entering the background.
    MyInf(@"Will enter foregroud");
    AlbumSync_Start();
}

- (void)applicationDidBecomeActive:(UIApplication *)application {
    // Restart any tasks that were paused (or not yet started) while the application was inactive. If the application was previously in the background, optionally refresh the user interface.
    MyInf(@"Did become active, restart location and sync");
//   [defaultAlbumSync startLocationMonitor];
    AlbumSync_Start();
}

- (void)applicationWillTerminate:(UIApplication *)application {
    // Called when the application is about to terminate. Save data if appropriate. See also applicationDidEnterBackground:.
    MyInf(@"Will terminate");
//    [defaultAlbumSync killMeNotification];
}

@end
