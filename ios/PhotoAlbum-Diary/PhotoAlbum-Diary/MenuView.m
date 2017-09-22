//
//  ViewController.m
//  PhotoAlbum-Diary
//
//  Created by Bin Yang on 6/15/16.
//  Copyright © 2016 Bin Yang. All rights reserved.
//

#import "MenuView.h"
#import "ScanerVC.h"
#import "SetupView.h"

enum {
    MENU_USER = 0,
    MENU_UTILS,
    MENU_SYS,
    MENU_NUM
};

enum {
    MENU_USER_INFO = 0,
    MENU_USER_NUM,
};

enum {
    MENU_UTILS_SCAN = 0,
    MENU_UTILS_LOCATION,
    MENU_UTILS_HEALTH,
    MENU_UTILS_NUM,
};

enum {
    MENU_SYS_SETUP = 0,
    MENU_SYS_HELP,
    MENU_SYS_VERSION,
    MENU_SYS_NUM,
};

@interface MenuView ()
@property BOOL navigationBarHiddenSave;

@end

@implementation MenuView

- (void)viewDidLoad {
    self.tableView.tableFooterView = [UIView new];
    [super viewDidLoad];
}

-(void) viewWillAppear:(BOOL)animated {
    self.navigationBarHiddenSave = self.navigationController.navigationBarHidden;
    self.navigationController.navigationBarHidden = NO;
    [self.tableView reloadData];
}

-(void) viewWillDisappear:(BOOL)animated {
    self.navigationController.navigationBarHidden = self.navigationBarHiddenSave;
    [super viewWillDisappear:animated];
}

#pragma mark - UITableViewDataSource

- (void)prepareForSegue:(UIStoryboardSegue *)segue sender:(id)sender {
    if (!sender)
        return;

    UITableViewCell *cell = sender;
    NSIndexPath *indexPath = [self.tableView indexPathForCell:cell];
    NSInteger section = indexPath.section;
    NSInteger row = indexPath.row;

    if (section == MENU_UTILS && row == MENU_UTILS_SCAN) {
        ScanerVC *scanViewController = segue.destinationViewController;
        scanViewController.scanResultHandler = ^BOOL (NSString *str, NSString **errString) {
            return [defaultAlbumSync scanHandle:str errString:errString];
        };
    }
    
}

- (NSInteger)numberOfSectionsInTableView:(UITableView *)tableView {
    return MENU_NUM;
}

- (NSInteger)tableView:(UITableView *)tableView numberOfRowsInSection:(NSInteger)section {
    NSInteger numberOfRows = 0;
    
    if (section == MENU_USER) {
        numberOfRows = MENU_USER_NUM;
    }
    if (section == MENU_UTILS) {
        numberOfRows = MENU_UTILS_NUM;
    }
    if (section == MENU_SYS) {
        numberOfRows = MENU_SYS_NUM;
    }

    return numberOfRows;
}

- (NSString *)tableView:(UITableView *)tableView titleForHeaderInSection:(NSInteger)section {
    if (section == MENU_USER) {
        return @"User";
    }
    if (section == MENU_UTILS) {
        return @"Utilities";
    }
    if (section == MENU_SYS) {
        return @"System";
    }

    return @"";
}

- (UITableViewCell *)tableView:(UITableView *)tableView cellForRowAtIndexPath:(NSIndexPath *)indexPath {
    UITableViewCell *cell = nil;

    NSInteger section = indexPath.section;
    NSInteger row = indexPath.row;
    
    if (section == MENU_USER) {
        cell = [tableView dequeueReusableCellWithIdentifier:@"MenuUserCell" forIndexPath:indexPath];
        if (row == MENU_USER_INFO) {
//            cell.imageView.image = [UIImage imageNamed:@"user"];
            UIImage *orgImg = [UIImage imageNamed:@"account.png"];
            UIImage *resizedImg = MyLib_ImageWithImage(orgImg, CGSizeMake(cell.textLabel.font.lineHeight, cell.textLabel.font.lineHeight));
            cell.imageView.image = resizedImg;
      
            if (setup_get_bool(SETUP_KEY_IS_BIND)) {
                cell.textLabel.text = [[NSString alloc] initWithFormat:@"%@ @ %@", setup_get_string(SETUP_KEY_USER_NAME), setup_get_string(SETUP_KEY_SERVER_URL)];
            }else {
                cell.textLabel.text = @"Please scan the QR code to login.";
            }
            cell.textLabel.numberOfLines = 0;
            cell.textLabel.lineBreakMode = NSLineBreakByWordWrapping;
            cell.accessoryType = UITableViewCellAccessoryNone;
            cell.userInteractionEnabled = NO;
        }
    }

    if (section == MENU_UTILS) {
        cell = [tableView dequeueReusableCellWithIdentifier:@"MenuUtilsCell" forIndexPath:indexPath];
        if (row == MENU_UTILS_SCAN) {
/*
            cell.imageView.image = [[UIImage imageNamed:@"scan.png"] imageWithRenderingMode:UIImageRenderingModeAlwaysTemplate];
            [cell.imageView setTintColor:[UIColor blueColor]];
            cell.imageView.contentMode = UIViewContentModeScaleAspectFit;
            cell.imageView.frame = CGRectMake(0, 0, cell.frame.size.height/2, cell.frame.size.height/2);
*/
            UIImage *orgImg = [UIImage imageNamed:@"camera.png"];
            UIImage *resizedImg = MyLib_ImageWithImage(orgImg, CGSizeMake(cell.textLabel.font.lineHeight, cell.textLabel.font.lineHeight));
            cell.imageView.image = resizedImg;
//            cell.imageView.image = orgImg;
            cell.textLabel.text = @"scan";
        }
        if (row == MENU_UTILS_HEALTH) {
//            cell.imageView.image = [UIImage imageNamed:@"health"];
            UIImage *orgImg = [UIImage imageNamed:@"heart.png"];
            UIImage *resizedImg = MyLib_ImageWithImage(orgImg, CGSizeMake(cell.textLabel.font.lineHeight, cell.textLabel.font.lineHeight));
            cell.imageView.image = resizedImg;

            cell.textLabel.text = @"health";
        }
        if (row == MENU_UTILS_LOCATION) {
//            cell.imageView.image = [UIImage imageNamed:@"footprint"];
            UIImage *orgImg = [UIImage imageNamed:@"location"];
            UIImage *resizedImg = MyLib_ImageWithImage(orgImg, CGSizeMake(cell.textLabel.font.lineHeight, cell.textLabel.font.lineHeight));
            cell.imageView.image = resizedImg;

            
            cell.textLabel.text = @"footprint";
        }
    }
    if (section == MENU_SYS) {
        cell = [tableView dequeueReusableCellWithIdentifier:@"MenuSysCell" forIndexPath:indexPath];
        if (row == MENU_SYS_SETUP) {
            UIImage *orgImg = [UIImage imageNamed:@"gear.png"];
            UIImage *resizedImg = MyLib_ImageWithImage(orgImg, CGSizeMake(cell.textLabel.font.lineHeight, cell.textLabel.font.lineHeight));
            cell.imageView.image = resizedImg;
            cell.textLabel.text = @"setup";
        }
        if (row == MENU_SYS_HELP) {
            UIImage *orgImg = [UIImage imageNamed:@"question.png"];
            UIImage *resizedImg = MyLib_ImageWithImage(orgImg, CGSizeMake(cell.textLabel.font.lineHeight, cell.textLabel.font.lineHeight));
            cell.imageView.image = resizedImg;
            cell.textLabel.text = @"help";
        }
        if (row == MENU_SYS_VERSION) {
            UIImage *orgImg = [UIImage imageNamed:@"label.png"];
            UIImage *resizedImg = MyLib_ImageWithImage(orgImg, CGSizeMake(cell.textLabel.font.lineHeight, cell.textLabel.font.lineHeight));
            cell.imageView.image = resizedImg;
            cell.textLabel.text = [[NSString alloc] initWithFormat:@"Family Cloud (build %d)", MY_VERSION];
        }
    }

    return cell;
}

- (void) tableView: (UITableView *) tableView didSelectRowAtIndexPath: (NSIndexPath *) indexPath {
    MyInf(@"click me: %@", indexPath);
     NSInteger section = indexPath.section;
     NSInteger row = indexPath.row;

    if (section == MENU_UTILS && row == MENU_UTILS_SCAN) {
        [self performSegueWithIdentifier:@"Menu_To_Scan" sender:[self.tableView cellForRowAtIndexPath:indexPath]];
    }
    if (section == MENU_SYS && row == MENU_SYS_SETUP) {
        [self performSegueWithIdentifier:@"Menu_To_Setup" sender:[self.tableView cellForRowAtIndexPath:indexPath]];
    }
}


@end


